<?php
/**
 * @category   Mad
 * @package    Mad_View
 * @subpackage Helper
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * @category   Mad
 * @package    Mad_View
 * @subpackage Helper
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_View_Helper_Text extends Mad_View_Helper_Base
{
    /**
     * @var null|Solar_Markdown
     */
    protected $_markdown;

    /**
     * @var null|Horde_Text_Textile
     */
    protected $_textile;

    /**
     * @var array
     */
    protected $_cycles = array();

    /**
     * Escapes a value for output in a view template. Technically
     * this is a helper method to be used in the views.
     *
     * <code>
     *   <p><?= $this->h($this->templateVar) ?></p>
     * </code>
     *
     * @param   mixed   $var The output to escape.
     * @return  mixed   The escaped value.
     */
    public function h($var)
    {
        return htmlentities($var, ENT_QUOTES, 'utf-8');
    }

    /** 
     * Returns the text with all the Markdown codes turned into HTML tags.
     *
     * @param  string  $text  Markdown
     * @return string         HTML
     */
    public function markdown($text)
    {
        if (! $this->_markdown) {
            $this->_markdown = new Solar_Markdown();
        }
        return $this->_markdown->transform($text);
    }

    /**
     * Returns the text with all the Textile codes turned into HTML tags.
     *
     * @param  string  $text  Textile
     * @return string         HTML
     */
    public function textilize($text)
    {
        if (! $this->_textile) {
            $this->_textile = new Horde_Text_Textile();
        }
        return $this->_textile->transform($text);
    }
    
    /**
     * Returns the text with all the Textile codes turned into HTML tags, 
     * but without the bounding <p> tag
     *
     * @param  string  $text  Textile
     * @return string         HTML
     */
    public function textilizeWithoutParagraph($text)
    {
        $textiled = $this->textilize($text);

        if (substr($textiled, 0, 3) == '<p>') {
            $textiled = substr($textiled, 3);
        }

        if (substr($textiled, -4) == '</p>') {
            $textiled = substr($textiled, 0, -4);
        }

        return $textiled;
    }
    
    public function pluralize($count, $singular, $plural = null)
    {
        if ($count == '1') {
            $word = $singular;
        } else if ($plural) {
            $word = $plural;
        } else {
            $word = Mad_Support_Inflector::pluralize($singular);
        }
        
        return "$count $word";
    }
    
    /**
     * Creates a Cycle object whose __toString() method cycles through elements of an
     * array every time it is called. This can be used for example, to alternate 
     * classes for table rows:
     *
     *   <? foreach($items as $item): ?>
     *     <tr class="<?= $this->cycle("even", "odd") ?>">
     *       <td>item</td>
     *     </tr>
     *   <% endforeach %>
     *
     * You can use named cycles to allow nesting in loops.  Passing an array as 
     * the last parameter with a <tt>name</tt> key will create a named cycle.
     * You can manually reset a cycle by calling resetCycle() and passing the 
     * name of the cycle.
     *
     *   <? foreach($items as $item): ?>
     *     <tr class="<?= $this->cycle("even", "odd", array('name' => "row_class")) ?>">
     *       <td>
     *         <? foreach ($item->values as $value) ?>
     *           <span style="color:<?= $this->cycle("red", "green", "blue", array('name' => "colors")) ?>">
     *             value
     *           </span>
     *         <% end %>
     *         <? $this->resetCycle("colors") ?>
     *       </td>
     *    </tr>
     *   <% endforeach %> 
     *
     */
    public function cycle($firstValue)
    {
        $values = func_get_args();

        $last = end($values);
        if (is_array($last)) {
            $options = array_pop($values);
            $name = isset($options['name']) ? $options['name'] : 'default';
        } else {
            $name = 'default';
        }

        if (empty($this->_cycles[$name]) || $this->_cycles[$name]->getValues() != $values) {
            $this->_cycles[$name] = new Mad_View_Helper_Text_Cycle($values);
        }
        return $this->_cycles[$name];
    }

    /**
     * Resets a cycle so that it starts from the first element the next time 
     * it is called. Pass in $name to reset a named cycle.
     * 
     * @param  string  $name  Name of cycle to reset (defaults to "default")
     * @return void
     */
    public function resetCycle($name = 'default')
    {
        if (isset($this->_cycles[$name])) {
            $this->_cycles[$name]->reset();
        }
    }

    /**
     * Highlights the phrase where it is found in the text by surrounding it like
     * <strong class="highlight">I'm highlighted</strong>. The Highlighter can
     * be customized by passing highlighter as a single-quoted string with $1
     * where the prhase is supposed to be inserted.
     *
     * @param   string  $text
     * @param   string  $phrase
     * @param   string  $highlighter
     */
    public function highlight($text, $phrase, $highlighter=null)
    {
        if (empty($highlighter)) {
            $highlighter='<strong class="highlight">$1</strong>';
        }
        if (empty($phrase) || empty($text)) {
            return $text;
        }
        return preg_replace("/($phrase)/", $highlighter, $text);
    }
    
    /**
     * If $text is longer than $length, $text will be truncated to the 
     * length of $length and the last three characters will be replaced 
     * with the $truncateString.
     * 
     * <code>
     * $this->truncate("Once upon a time in a world far far away", 14);
     * => Once upon a...
     * </code>
     * 
     * @param   string  $text
     * @param   integer $length
     * @param   string  $truncateString
     * @return  string
     */
    public function truncate($text, $length=30, $truncateString = '...')
    {
        if (empty($text)) { return $text; }
        $l = $length - strlen($truncateString);
        return strlen($text) > $length ? substr($text, 0, $l).$truncateString : $text;
    }

    /**
     * Limit a string to a given maximum length in a smarter way than just using
     * substr. Namely, cut from the MIDDLE instead of from the end so that if
     * we're doing this on (for instance) a bunch of binder names that start off
     * with the same verbose description, and then are different only at the
     * very end, they'll still be different from one another after truncating.
     *
     * <code>
     *  <?php
     *  ...
     *  $str = "The quick brown fox jumps over the lazy dog tomorrow morning.";
     *  $shortStr = truncateMiddle($str, 40);
     *  // $shortStr = "The quick brown fox... tomorrow morning."
     *  ...
     *  ?>
     * </code>
     *
     * @todo    This is not a Rails helper...
     * @param   string  $str
     * @param   int     $maxLength
     * @param   string  $joiner
     * @return  string
     */
    public function truncateMiddle($str, $maxLength=80, $joiner='...')
    {
        if (strlen($str) <= $maxLength) {
            return $str;
        }
        $maxLength = $maxLength - strlen($joiner);
        if ($maxLength <= 0) {
            return $str;
        }
        $startPieceLength = (int) ceil($maxLength / 2);
        $endPieceLength = (int) floor($maxLength / 2);
        $trimmedString = substr($str, 0, $startPieceLength) . $joiner;
        if ($endPieceLength > 0) {
            $trimmedString .= substr($str, (-1 * $endPieceLength));
        }
        return $trimmedString;
    }

    /**
     * Allow linebreaks in a string after slashes or underscores
     *
     * @todo    This is not a Rails helper...
     * @param   string  $str
     * @return  string
     */
    public function makeBreakable($str)
    {
        return str_replace(
            array('/',      '_'),
            array('/<wbr>', '_<wbr>'),
            $str
        );
    }
}
