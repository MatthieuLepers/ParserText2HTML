<?php
	/**
	 * Represents a HTML tag
	 * @author : Matthieu Lepers (Aire Ayquaza)
	 * @version 1.0.0
	 */
	class Tag
	{
		private $tagName;
		private static $OPRH = array('img', 'link', 'meta', 'br', 'hr', 'input', 'area', 'param');
		private $attributes  = array();
		private $childrens   = array();
		private $content;
		
		/**
		 * Create a representation of a HTML tag
		 * @param tagName : [String] The tag name
		 */
		function __construct($tagName)
		{
			$this->tagName = strtolower($tagName);
		}
		
		/**
		 * Make a tag from data
		 * @param tagName       : [String] The tag name
		 * @param tagId         : [String] The id attribute to set
		 * @param tagClass      : [Array]  The class attribute to set as an array
		 * @param tagAttributes : [Array]  The other attributes to set
		 * @param pageContent   : [String] The content of the tag
		 * @return              : [Tag] The tag object
		 */
		public static function makeTag($tagName, $tagId, $tagClass, $tagAttributes, $tagContent)
		{
			$tag = new Tag($tagName);
			if ($tagId != '') $tag->addAttribute('id', $tagId);
			for ($j = 1; $j < sizeof($tagClass); $j++)
				$tag->addAttribute('class', $tagClass[$j]);
			for ($j = 0; $j < sizeof($tagAttributes); $j++)
			{
				$attr  = preg_replace('#([^ ]+)=\"([^\"]+)\"#', '$1', $tagAttributes[$j]);
				$value = preg_replace('#([^ ]+)=\"([^\"]+)\"#', '$2', $tagAttributes[$j]);
				$tag->addAttribute($attr, $value);
			}
			$tag->addTextContent($tagContent);
			
			return $tag;
		}
		
		/* ------ Setters ----- */
		/**
		 * Add an attribute to the tag
		 * @param attr  : [String] The attribute name
		 * @param value : [String] The attribute value
		 */
		public function addAttribute($attr, $value)
		{
			if (!isset($this->attributes[$attr]))
				$this->attributes[$attr] = $value;
			else
				$this->attributes[$attr] .= ' ' . $value;
		}
		
		/**
		 * Add a content to the tag
		 * @param text : [String] The content
		 */
		public function addTextContent($text)
		{
			$this->content = $text;
		}
		
		/**
		 * Add a child into the tag (not ordered)
		 * @param child : [Tag] The tag to add as a child
		 */
		public function appendChild(Tag $child, $depth = 0)
		{
			if ($depth == 0)
				$this->childrens[] = $child;
			else
				$this->childrens[sizeof($this->childrens) - 1]->appendChild($child, $depth - 1);
		}
		
		/* ----- Booleans ----- */
		/**
		 * Check if the tag is an oprphan tag or not
		 * @return : [Boolean] true if the tag is an oprphan tag, false if not
		 */
		private function isOprh()
		{
			return in_array($this->tagName, Tag::$OPRH);
		}
		
		/* ----- Render ----- */
		/**
		 * Build the tag as a HTML string
		 * @return : [String] The HTML string
		 */
		public function build()
		{
			$res = "<{$this->tagName}";
			
			if (sizeof($this->attributes) > 0)
				foreach ($this->attributes as $key => $value)
					$res .= " {$key}=\"{$value}\"";
			
			if (!$this->isOprh())
				$res .= ">{$this->content}";
			else
				return $res . " />";
			
			if (!$this->isOprh())
				for ($i = 0; $i < sizeof($this->childrens); $i++)
					$res .= "\n\t{$this->childrens[$i]->build()}";
			
			$n = (sizeof($this->childrens) > 0 ? "\n" : "");
			
			return $res . (!$this->isOprh() ? "{$n}</{$this->tagName}>" : "");
		}
	}
	
	/**
	 * Represents the parser to convert text to HTML
	 * @author : Matthieu Lepers (Aire Ayquaza)
	 * @version 1.0.1
	 */
	class ParserText2HTML
	{
		private $fileContent;
		
		/**
		 * Create the parser
		 * @param file : [String] The file to convert
		 */
		function __construct($file)
		{
			$this->fileContent = file_get_contents($file);
		}
		
		/**
		 * Process the passed array into a valid attribute array
		 * @param tab : [Array] The unvalidated attribute array
		 * @return    : [Array] The validated attribute array
		 */
		private function process($tab)
		{
			$res = array();
			$tmp = '';
			
			for ($i = 1; $i < sizeof($tab); $i++)
			{
				if (preg_match('#[^ ]+=\"[^\"]+\"#', $tab[$i]))
					$res[] = $tab[$i];
				else if(!preg_match('#[^ ]+=\"[^\"]+\"#', $tab[$i]) && $tab[$i]{strlen($tab[$i]) - 1} == '"')
				{
					$res[] = $tmp . $tab[$i];
					$tmp = '';
				}
				else
					$tmp .= $tab[$i] . ' ';
			}
			
			return $res;
		}
		
		/**
		 * Convert the file content to HTML
		 */
		public function parse()
		{
			$regexTags = 'abbr|address|area|article|aside|audio|a|base|bdo|blockquote|body|br|button|b|canvas|caption|cite|code|colgroup|col|command|datalist|dd|del|defs|details|dfn|div|dl|dt|embed|em|fieldset|figcaption|figure|footer|form|h1|h2|h3|h4|h5|header|head|hgroup|hr|html|iframe|img|input|ins|i|keygen|kbd|label|legend|link|li|map|mark|math|menu|meta|meter|nav|noscript|object|ol|optgroup|option|output|path|param|pre|progress|p|q|rp|rt|ruby|samp|script|section|select|small|source|span|strong|style|sub|summary|sup|svg|symbol|table|tbody|td|textarea|tfoot|thead|th|time|title|tr|track|ul|use|var|video|wbr';
			$regexId      = "(#([^ .]+))?";
			$regexClass   = "((\.[^ ]+)+)?";
			$regexAttribute   = "(( [^ ]+=\"[^\"]+\")+)?";
			$regexTextContent = "( (.+))?";
			
			$regex = "({$regexTags}){$regexId}{$regexClass}{$regexAttribute}{$regexTextContent}";
			
			$tab = explode("\r\n", $this->fileContent);
			$tags = array();
			
			for ($i = 0; $i < sizeof($tab); $i++)
			{
				if (preg_match("%( +){$regex}%", $tab[$i]))
				{
					$depth         = strlen(preg_replace("%( +){$regex}%", '$1', $tab[$i]));
					$tagName       = preg_replace("%( +){$regex}%", '$2', $tab[$i]);
					$tagId         = preg_replace("%( +){$regex}%", '$4', $tab[$i]);
					$tagClass      = explode('.', preg_replace("%( +){$regex}%", '$6', $tab[$i]));
					$tagAttributes = $this->process(explode(' ', preg_replace("%( +){$regex}%", '$7', $tab[$i])));
					$tagContent    = preg_replace("%( +){$regex}%", '$10', $tab[$i]);
					
					$tag = Tag::makeTag($tagName, $tagId, $tagClass, $tagAttributes, $tagContent);
					
					$tags[sizeof($tags) - 1]->appendChild($tag, $depth - 1);
				}
				else if (preg_match("%{$regex}%", $tab[$i]))
				{
					$tagName       = preg_replace("%{$regex}%", '$1', $tab[$i]);
					$tagId         = preg_replace("%{$regex}%", '$3', $tab[$i]);
					$tagClass      = explode('.', preg_replace("%{$regex}%", '$5', $tab[$i]));
					$tagAttributes = $this->process(explode(' ', preg_replace("%{$regex}%", '$6', $tab[$i])));
					$tagContent    = preg_replace("%{$regex}%", '$9', $tab[$i]);
					
					$tag = Tag::makeTag($tagName, $tagId, $tagClass, $tagAttributes, $tagContent);
					$tags[] = $tag;
				}
			}
			
			$result = '';
			for ($i = 0; $i < sizeof($tags); $i++)
				$result .= $tags[$i]->build() . "\n";
			
			return substr($result, 0, strlen($result) - 1);
		}
		
		/**
		 * Convert the file content to HTML into an output file
		 * @param outFile : [String] The out file
		 */
		public function parseAsFile($outFile)
		{
			touch($outFile);
			file_put_contents($outFile, $this->parse());
		}
	}
	
	/* ----- Test ----- */
	/**
	 * You can test this file by adding the followinf line into the URL :
	 * ?file={fileToParse}
	 */
	if (isset($_GET['file']) && file_exists($_GET['file']))
	{
		$parser = new ParserText2HTML($_GET['file']);
		echo $parser->parse();
	}
	else
		echo 'No files to test';
?>