<?php # vim:ts=2:sw=2:noet:
/* Copyright (c) 2007, OmniTI Computer Consulting, Inc.
 * All Rights Reserved.
 * For licensing information, see:
 * http://labs.omniti.com/alexandria/trunk/LICENSE
 */

/** 
 * A class for working with RFC 4122 UUIDs
 */
class OmniTI_Util_UUID {
	public $binary;

	function __construct($src = null) {
		if ($src !== null) {
			switch (strlen($src)) {
				case 36: /* with -'s */
					$src = str_replace('-', '', $src);
				case 32: /* with -'s stripped */
					$this->binary = pack('H*', $src);
					break;
				case 16: /* binary string */
					$this->binary = $src;
					break;
				case 24: /* base64 encoded binary */
					$this->binary = base64_decode(
						str_replace(
							array('@', '-', '_'),
							array('=', '/', '+'),
							$src));
					break;
				default:
					$this->binary = null;
			}
		} else {
			$this->generate();
		}
	}

	/** 
	 * returns a 32-bit integer that identifies this host.
	 * The node identifier needs to be unique among nodes
	 * in a cluster for a given application in order to
	 * avoid collisions between generated identifiers.
	 * You may extend and override this method if you
	 * want to substitute an alternative means of determining
	 * the node identifier */
	protected function getNodeId() {
		if (isset($_SERVER['SERVER_ADDR'])) {
			$node = ip2long($_SERVER['SERVER_ADDR']);
		} else {
			/* running from the CLI most likely;
			 * inspect the environment to see if we can
			 * deduce the hostname, and from there, the
			 * IP address */
			static $names = array('HOSTNAME', 'HOST');
			foreach ($names as $name) {
				if (isset($_ENV[$name])) {
					$host = $_ENV[$name];
				} else {
					$host = getenv($name);
				}
				if (strlen($host)) break;
			}
			if (!strlen($host)) {
				// punt	
				$node = ip2long('127.0.0.1');
			} else {
				$ip = gethostbyname($host);
				if (strlen($ip)) {
					$node = ip2long($ip);
				} else {
					// punt
					$node = crc32($host);
				}
			}
		}
		return $node;
	}

	/** 
	 * returns a process identifier.
	 * In multi-process servers, this should be the system process ID.
	 * In multi-threaded servers, this should be some unique ID to
	 * prevent two threads from generating precisely the same UUID
	 * at the same time.
	 */
	protected function getLockId() {
		if (function_exists('zend_thread_id')) {
			return zend_thread_id();
		}
		return getmypid();
	}

	/** 
	 * generate an RFC 4122 UUID.
	 * This is psuedo-random UUID influenced by the system clock, IP
	 * address and process ID. 
	 *
	 * The intended use is to generate an identifier that can uniquely
	 * identify user generated posts, comments etc. made to a website.
	 * This generation process should be sufficient to avoid collisions
	 * between nodes in a cluster, and between apache children on the
	 * same host.
	 *
	 */
	function generate() {
		$node = $this->getNodeId();
		$pid = $this->getLockId();

		list($time_mid, $time_lo) = explode(' ', microtime());
		$time_lo = (int)$time_lo;
		$time_mid = (int)substr($time_mid, 2);

		$time_hi = mt_rand(0, 0xfff);
		/* version 4 UUID */
		$time_hi |= 0x4000;

		$clock_lo = mt_rand(0, 0xff);
		$node_lo = $pid;

		/* type is psuedo-random */
		$clock_hi = mt_rand(0, 0x3f);
		$clock_hi |= 0x80;

		$this->binary = pack('NnnCCnN',
			$time_lo, $time_mid & 0xffff, $time_hi,
			$clock_hi, $clock_lo, $node_lo, $node);
	}

	/** 
	 * render the UUID as an RFC4122 standard string representation
	 * of the binary bits.
	 */
	function toRFC4122String() {
		$uuid = unpack('Ntl/ntm/nth/Cch/Ccl/nnl/Nn', $this->binary);
		return sprintf("%08x-%04x-%04x-%02x%02x-%04x%08x",
			$uuid['tl'], $uuid['tm'], $uuid['th'],
			$uuid['ch'], $uuid['cl'], $uuid['nl'], $uuid['n']);
	}

	/** 
	 * render the UUID using a modified base64 representation
	 * of the binary bits.  This string is shorter than the standard
	 * representation, but is not part of any standard specification.
	 */
	function toShortString() {
		return str_replace(
				array('=', '/', '+'),
				array('@', '-', '_'),
				base64_encode($this->binary));
	}
}
