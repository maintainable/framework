<?php
/**
 * @category   Mad
 * @package    Mad_View
 * @subpackage Helper
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */


/**
 * This class provides methods for generating HTML that links views to assets such
 * as images, javascripts, stylesheets, and feeds. These methods do not verify 
 * the assets exist before linking to them. 
 *
 * === Using asset hosts
 * By default, Rails links to these assets on the current host in the public
 * folder, but you can direct Rails to link to assets from a dedicated assets server by 
 * setting ActionController::Base.asset_host in your environment.rb.  For example,
 * let's say your asset host is assets.example.com. 
 *
 *   ActionController::Base.asset_host = "assets.example.com"
 *   image_tag("rails.png")
 *     => <img src="http://assets.example.com/images/rails.png" alt="Rails" />
 *   stylesheet_include_tag("application")
 *     => <link href="http://assets.example.com/stylesheets/application.css" media="screen" rel="stylesheet" type="text/css" />
 *
 * This is useful since browsers typically open at most two connections to a single host,
 * which means your assets often wait in single file for their turn to load.  You can
 * alleviate this by using a %d wildcard in <tt>asset_host</tt> (for example, "assets%d.example.com") 
 * to automatically distribute asset requests among four hosts (e.g., assets0.example.com through assets3.example.com)
 * so browsers will open eight connections rather than two.  
 *
 *   image_tag("rails.png")
 *     => <img src="http://assets0.example.com/images/rails.png" alt="Rails" />
 *   stylesheet_include_tag("application")
 *     => <link href="http://assets3.example.com/stylesheets/application.css" media="screen" rel="stylesheet" type="text/css" />
 *
 * To do this, you can either setup four actual hosts, or you can use wildcard DNS to CNAME 
 * the wildcard to a single asset host.  You can read more about setting up your DNS CNAME records from
 * your ISP.
 *
 * Note: This is purely a browser performance optimization and is not meant
 * for server load balancing. See http://www.die.net/musings/page_load_time/
 * for background.
 *
 * @category   Mad
 * @package    Mad_View
 * @subpackage Helper
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_View_Helper_AssetTag extends Mad_View_Helper_Base
{
    /**
     * All stylesheet filenames in the stylesheets/ directory
     * @var null|array
     */
    protected $_allStylesheetSources;
    
    /**
     * Returns the directory where the assets are stored.
     * @return string
     */
    public function getAssetsDir() 
    {
        return defined('MAD_ROOT') ? MAD_ROOT.'/public' : 'public';
    }
    
    /**
     * Returns the directory where the Javascript assets are stored.
     * @return string
     */
    public function getJavascriptsDir() 
    {
        $assetsDir = $this->getAssetsDir();
        return "$assetsDir/javascripts";
    }

    /**
     * Returns the directory where the stylesheet assets are stored.
     * @return string
     */
    public function getStylesheetsDir() 
    {
        $assetsDir = $this->getAssetsDir();
        return "$assetsDir/stylesheets";
    }

    /**
     * Computes the path to a javascript asset in the public javascripts directory.
     * If the +source+ filename has no extension, .js will be appended.
     * Full paths from the document root will be passed through.
     * Used internally by javascript_include_tag to build the script path.
     *
     * ==== Examples
     *   javascriptPath("xmlhr")                                       # => /javascripts/xmlhr.js
     *   javascriptPath("dir/xmlhr.js")                                # => /javascripts/dir/xmlhr.js
     *   javascriptPath("/dir/xmlhr")                                  # => /dir/xmlhr.js
     *   javascriptPath("http://www.railsapplication.com/js/xmlhr")    # => http://www.railsapplication.com/js/xmlhr.js
     *   javascriptPath("http://www.railsapplication.com/js/xmlhr.js") # => http://www.railsapplication.com/js/xmlhr.js
     *
     * @param  string  $source  Source filename
     * @return string           Computed path to source filename
     */
    public function javascriptPath($source)
    {
        return $this->_computePublicPath($source, 'javascripts', 'js');
    }
    
    /**
     * Returns an array with the default Javascript sources.
     *
     * @return array
     */
    public function getJavascriptDefaultSources() 
    {
        return array('prototype', 'effects', 'dragdrop', 'controls');
    }

    /**
     * Computes the path to a stylesheet asset in the public stylesheets directory.
     * If the +source+ filename has no extension, .css will be appended.
     * Full paths from the document root will be passed through.
     * Used internally by stylesheet_link_tag to build the stylesheet path.
     *
     * ==== Examples
     *   stylesheetPath("style") # => /stylesheets/style.css
     *   stylesheetPath("dir/style.css") # => /stylesheets/dir/style.css
     *   stylesheetPath("/dir/style.css") # => /dir/style.css
     *   stylesheetPath("http://www.railsapplication.com/css/style") # => http://www.railsapplication.com/css/style.css
     *   stylesheetPath("http://www.railsapplication.com/css/style.js") # => http://www.railsapplication.com/css/style.css
     */
    public function stylesheetPath($source)
    {
        return $this->_computePublicPath($source, 'stylesheets', 'css');        
    }

    /**
     * Returns a stylesheet link tag for the sources specified as arguments. If
     * you don't specify an extension, .css will be appended automatically.
     * You can modify the link attributes by passing a hash as the last argument.
     *
     * ==== Examples
     *   stylesheetLinkTag("style") # =>
     *     <link href="/stylesheets/style.css" media="screen" rel="stylesheet" type="text/css" />
     *
     *   stylesheetLinkTag("style.css") # =>
     *     <link href="/stylesheets/style.css" media="screen" rel="stylesheet" type="text/css" />
     *
     *   stylesheetLinkTag("http://www.railsapplication.com/style.css") # =>
     *     <link href="http://www.railsapplication.com/style.css" media="screen" rel="stylesheet" type="text/css" />
     *
     *   stylesheetLinkTag("style", :media => "all") # =>
     *     <link href="/stylesheets/style.css" media="all" rel="stylesheet" type="text/css" />
     *
     *   stylesheetLinkTag("style", :media => "print") # =>
     *     <link href="/stylesheets/style.css" media="print" rel="stylesheet" type="text/css" />
     *
     *   stylesheetLinkTag("random.styles", "/css/stylish") # =>
     *     <link href="/stylesheets/random.styles" media="screen" rel="stylesheet" type="text/css" />
     *     <link href="/css/stylish.css" media="screen" rel="stylesheet" type="text/css" />
     *
     * You can also include all styles in the stylesheet directory using 'all' as the source:
     *
     *   stylesheetLinkTag('all') # =>
     *     <link href="/stylesheets/style1.css"  media="screen" rel="stylesheet" type="text/css" />
     *     <link href="/stylesheets/styleB.css"  media="screen" rel="stylesheet" type="text/css" />
     *     <link href="/stylesheets/styleX2.css" media="screen" rel="stylesheet" type="text/css" />
     *
     * == Caching multiple stylesheets into one
     *
     * You can also cache multiple stylesheets into one file, which requires less HTTP connections and can better be
     * compressed by gzip (leading to faster transfers). Caching will only happen if 'perform_caching'
     * is set to true (which is the case by default for the Mad production environment, but not for the development
     * environment). Examples:
     *
     * ==== Examples
     *   stylesheetLinkTag('all', array('cache' => true)) # when 'perform_caching' is false =>
     *     <link href="/stylesheets/style1.css"  media="screen" rel="stylesheet" type="text/css" />
     *     <link href="/stylesheets/styleB.css"  media="screen" rel="stylesheet" type="text/css" />
     *     <link href="/stylesheets/styleX2.css" media="screen" rel="stylesheet" type="text/css" />
     *
     *   stylesheetLinkTag('all', array('cache' => true)) # when 'perform_caching' is true =>
     *     <link href="/stylesheets/all.css"  media="screen" rel="stylesheet" type="text/css" />
     *
     *   stylesheetLinkTag("shop", "cart", "checkout", array('cache' => "payment")) # when 'perform_caching' is false =>
     *     <link href="/stylesheets/shop.css"  media="screen" rel="stylesheet" type="text/css" />
     *     <link href="/stylesheets/cart.css"  media="screen" rel="stylesheet" type="text/css" />
     *     <link href="/stylesheets/checkout.css" media="screen" rel="stylesheet" type="text/css" />
     *
     *   stylesheetLinkTag("shop", "cart", "checkout", array('cache' => "payment")) # when ActionController::Base.perform_caching is true =>
     *     <link href="/stylesheets/payment.css"  media="screen" rel="stylesheet" type="text/css" />
     *
     * @todo caching needs to be implemented
     */
    public function stylesheetLinkTag($sources) 
    {
        $sources = func_get_args();
        $options = (is_array(end($sources))) ? array_pop($sources) : array();
        
        $sources = $this->_expandStylesheetSources($sources);
        $tags = array();
        foreach ($sources as $source) {
            $defaults = array('rel'   => 'stylesheet',
                              'type'  => 'text/css',
                              'media' => 'screen',
                              'href'  => $this->h($this->stylesheetPath($source)));
            $tags[] = $this->tag('link', array_merge($defaults, $options), false, false);
        }
        $tags = implode("\n", $tags);
        return $tags;
    }
    
    /**
     * @todo caching needs to be implemented
     */
    public function javascriptIncludeTag($sources)
    {
        $sources = func_get_args();
        $options = (is_array(end($sources))) ? array_pop($sources) : array();
   
        $sources = $this->_expandJavascriptSources($sources);

        $tags = array();
        foreach ($sources as $source) {
            $defaults = array('type'  => 'text/javascript',
                              'src'   => $this->javascriptPath($source));
            $tags[] = $this->contentTag('script', '', array_merge($defaults, $options));
        }                
        $tags = implode("\n", $tags);
        return $tags;
    }
    
    /**
     * Computes the path to an image asset in the public images directory.
     * Full paths from the document root will be passed through.
     * Used internally by image_tag to build the image path.
     *
     * ==== Examples
     *   image_path("edit")                                         # => /images/edit
     *   image_path("edit.png")                                     # => /images/edit.png
     *   image_path("icons/edit.png")                               # => /images/icons/edit.png
     *   image_path("/icons/edit.png")                              # => /icons/edit.png
     *   image_path("http://www.railsapplication.com/img/edit.png") # => http://www.railsapplication.com/img/edit.png
     * 
     * @todo implement this
     * @param   string  $source
     */
    public function imagePath($source)
    {
        return $this->_computePublicPath($source, 'images');
    }
    
    /**
     * Returns an html image tag for the +source+. The +source+ can be a full
     * path or a file that exists in your public images directory.
     *
     * ==== Options
     * You can add HTML attributes using the +options+. The +options+ supports
     * two additional keys for convienence and conformance:
     *
     * * <tt>:alt</tt>  - If no alt text is given, the file name part of the
     *   +source+ is used (capitalized and without the extension)
     * * <tt>:size</tt> - Supplied as "{Width}x{Height}", so "30x45" becomes
     *   width="30" and height="45". <tt>:size</tt> will be ignored if the
     *   value is not in the correct format.
     *
     * ==== Examples
     *  $this->imageTag("icon")  # =>
     *    <img src="/images/icon" alt="Icon" />
     * 
     *  $this->imageTag("icon.png")  # =>
     *    <img src="/images/icon.png" alt="Icon" />
     * 
     *  $this->imageTag("icon.png", :size => "16x10", :alt => "Edit Entry")  # =>
     *    <img src="/images/icon.png" width="16" height="10" alt="Edit Entry" />
     * 
     *  $this->imageTag("/icons/icon.gif", :size => "16x16")  # =>
     *    <img src="/icons/icon.gif" width="16" height="16" alt="Icon" />
     * 
     *  $this->imageTag("/icons/icon.gif", :height => '32', :width => '32') # =>
     *    <img alt="Icon" height="32" src="/icons/icon.gif" width="32" />
     * 
     *  $this->imageTag("/icons/icon.gif", :class => "menu_icon") # =>
     *    <img alt="Icon" class="menu_icon" src="/icons/icon.gif" />
     */
    public function imageTag($source, $options = array())
    {
        $options['src'] = $this->imagePath($source);
        $options['alt'] = isset($options['alt']) ? $options['alt'] : '';
        
        // set alt based on src name
        if (empty($options['alt'])) {
            // pathinfo() has pathinfo_filename support only since php 5.2
            if (defined('PATHINFO_FILENAME')) {
                $name = pathinfo($options['src'], PATHINFO_FILENAME);
            } else {
                // pathinfo() array keys are not guaranteed to be present
                $pathstub = array('basename' => '', 'extension' => '');
                $pathinfo = array_merge($pathstub, pathinfo($options['src']));

                // "logo.gif?1190248363" -> "logo"
                $name = substr($pathinfo['basename'], 0, -(strlen($pathinfo['extension'])+1));
            }
            
            $options['alt'] = ucfirst($name);
        }

        // calc width/height
        if (isset($options['size'])) {
            if (preg_match('/^\d+x\d+$/', $options['size'])) {
                $dimensions = explode('x', $options['size']);
                list($options['width'], $options['height']) = $dimensions;
            }
            unset($options['size']);
        }
        return $this->tag("img", $options);
    }
    
    /**
     * Add the .ext if not present. Return full URLs otherwise untouched.
     * Prefix with /dir/ if lacking a leading /. Account for relative URL
     * roots. Rewrite the asset path for cache-busting asset ids. Include
     * a single or wildcarded asset host, if configured, with the correct
     * request protocol.
     */
    protected function _computePublicPath($source, $dir, $ext = null, $includeHost = true) 
    {
        $extension = pathinfo($source, PATHINFO_EXTENSION);
        if (empty($extension) && !empty($ext)) {
            $source .= ".$ext";
        }
        
        if (preg_match("/^[-a-z]+:\/\//", $source)) {
            return $source;
        } else {
             if (substr($source, 0, 1) != '/') {
                 $source = "/$dir/$source";
             }
             $source = $this->_rewriteAssetPath($source);
             
             if ($includeHost) {
                 $host = $this->_computeAssetHost($source);
                 if (!empty($host) && !preg_match("/^[-a-z]+:\/\//", $host)) {
                     $host = $this->controller->getRequest()->getProtocol().$host;
                 }
                 return $host.$source;
             } else {
                 return $source;
             }
        }
    }

    /**
     * Pick an asset host for this source. Returns nil if no host is set,
     * the host if no wildcard is set, or the host interpolated with the
     * numbers 0-3 if it contains %d. The number is the source hash mod 4.
     * 
     * @todo    implement
     * @param   string  $source
     */
    protected function _computeAssetHost($source)
    {
        return null;
    }

    /**
     * Use the MAD_ASSET_ID environment variable or the source's
     * modification time as its cache-busting asset id.
     * 
     * @todo    implement
     * @param   string  $source
     */
    protected function _madAssetId($source)
    { 
        if (isset($_SERVER['MAD_ASSET_ID'])) {
            return $_SERVER['MAD_ASSET_ID'];
        }
        return @filemtime($this->getAssetsDir() . "/$source");
    }
    
    /**
     * Break out the asset path rewrite so you wish to put the asset id
     * someplace other than the query string.
     * 
     * @param   string  $source
     */
    protected function _rewriteAssetPath($source)
    {
        $assetId = $this->_madAssetId($source);
        if (!empty($assetId)) { $source .= "?$assetId"; }
        return $source;
    }

    /**
     * @todo finish defaults
     */
    protected function _expandJavascriptSources($sources)
    {
        if (in_array('all', $sources)) {
            $dir = $this->getJavascriptsDir();
            $allJavascriptFiles = $this->_filesInDirWithExt($dir, 'js');
            
            $sources = array_intersect($allJavascriptFiles, $this->getJavascriptDefaultSources());
            $sources = array_merge($sources, $allJavascriptFiles);
            
        } else if (in_array('defaults', $sources)) {
            // @todo finish defaults
        }

        return $sources;
    }
    
    /**
     * @todo in the future these should be cached on the server
     */
    protected function _expandStylesheetSources($sources)
    {
        if ($sources[0] != 'all') { return $sources; }

        if ($this->_allStylesheetSources === null) {
            $dir = $this->getStylesheetsDir();
            $this->_allStylesheetSources = $this->_filesInDirWithExt($dir, 'css');
        }
        return $this->_allStylesheetSources;
    }

    /**
     * Returns a sorted array of files in $path that have an extension $ext.
     *
     * @param  string  $path  Path to directory
     * @param  string  $ext   Extension to find ('ext' not '.ext')
     * @return array          Array of files, or empty array
     */
    protected function _filesInDirWithExt($path, $ext)
    {
        $files = array();
        $extpos = -(strlen($ext) +1);
        foreach(new DirectoryIterator($path) as $f) {
            $filename = $f->getFilename();
            if ($f->isFile() && substr($filename, $extpos) == ".$ext") {
                $files[] = basename($filename);
            }            
        }        
        sort($files);
        return $files;
    }
}
