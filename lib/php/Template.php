<?php
namespace PhenLib;

abstract class Template
{
	static private $init = FALSE;
	static private $doc = NULL;
	static private $hooks = NULL;

	abstract protected function __construct();

	static private function init()
	{
		if( self::$init )
			return;
		self::$init = TRUE;

		$di = new \DOMImplementation();
		$doc = self::$doc = $di->createDocument( "http://www.w3.org/1999/xhtml", "html", $di->createDocumentType(
			"html", "", "" ) );
		$doc->encoding = "utf-8";
		$doc->formatOutput = TRUE;

		//html
		$html = $doc->documentElement;
		$html->setAttribute("xmlns", "http://www.w3.org/1999/xhtml");

		//head
		$head = $doc->createElement( "head" );
		$html->appendChild( $head );

		$title = $doc->createElement( "title" );
		$head->appendChild( $title );

		$title_text = $doc->createTextNode( "Phenomena" );
		$title->appendChild( $title_text );

		//body
		$body = $doc->createElement( "body" );
		$html->appendChild( $body );

		self::$hooks = array(
			"head" => $head,
			"body" => $body
		);
	}

	static public function getDOC()
	{
		self::init();

		return self::$doc;
	}

	static public function setBaseURL( $baseURL )
	{
//TODO - shouldnt be set multiple times - if used persist and reuse base obj, just reset attrib
		$base = self::$doc->createElement( "base" );
		$base->setAttribute( "href", $baseURL );
		self::$hooks['head']->appendChild( $base );
	}

	static public function linkCSS( $css, $media="screen" )
	{
		$link = self::$doc->createElement( "link" );
		$link->setAttribute( "rel", "stylesheet" );
		$link->setAttribute( "type", "text/css" );
		$link->setAttribute( "media", $media );
		$link->setAttribute( "href", $css );
		self::$hooks['head']->appendChild( $link );
	}

	static public function scriptExternal( $js )
	{
		$script = self::$doc->createElement( "script" );
		$script->setAttribute( "type", "text/javascript" );
		$script->setAttribute( "src", $js );
		self::$hooks['head']->appendChild( $script );
	}

	static public function scriptLocal( $js )
	{
		$script = self::$doc->createElement( "script" );
		$script->setAttribute( "type", "text/javascript" );
		$script->appendChild( self::$doc->createComment( "\n{$js}\n" ) );
		self::$hooks['head']->appendChild( $script );
	}

	static public function integrate( $hook, $res )
	{
		self::init();

		//encapsulate non-queue resources into queue
		if( ! $res instanceof \SPLQueue )
		{
			$tmp = $res;
			$res = new \SPLQueue();
			$res->enqueue( $tmp );
		}

		foreach( $res as $obj )
		{
			if( ! $obj instanceof Displayable )
				throw new \Exception( "Template cannot integrate non-pluggable object" );
	
			self::appendDOM( $hook, $obj->getDOC() );
		
			//merge in hooks from templatable objects
			if( $obj instanceof Templatable )
			{
				$hooks = $obj->getHooks();
	
				if( ! is_array( $hooks ) )
					throw new \Exception( "Templatable function getHooks() must return hooks array" );

				foreach( $hooks as $key=>$val )
					self::$hooks[$key] = $val;
			}
		}
	}

	static public function appendDOM( $hook, $dom )
	{
		self::init();

		if( ! isset( self::$hooks[$hook] ) )
			throw new \Exception( "Invalid hook: '{$hook}'" );

		Displayable::generateAllOutput();
		self::$hooks[$hook]->appendChild( $dom );
	}

	static public function HTMLtoDOM( $html )
	{
		self::init();

		$frag = self::$doc->createDocumentFragment();
		if( $frag->appendXML( $html ) === FALSE )
			throw new \Exception( "failed to parse html" );
		return $frag;
	}

	static public function display()
	{
		self::init();

		//TODO 	- follow up on this, choose a better name, input id at top of form instead of bottom
		//	- WARN:  this will likely not work when actions are inside of other actions (shouldnt happen but add a check sometime)
		$xp = new \DOMXPath( self::$doc );
		$actionDivs = $xp->query( "//div[contains(concat(' ',normalize-space(@class),' '),' PhenLib-Action ')]" );
		foreach( $actionDivs as $actionDiv )
		{
			$id = $actionDiv->getAttribute( "id" );

			$input = self::$doc->createElement( "input" );
			$input->setAttribute( "type", "hidden" );
			$input->setAttribute( "name", "id" );
			$input->setAttribute( "value", $id );

			$fieldset = self::$doc->createElement( "fieldset" );
			$fieldset->appendChild( $input );

			$forms = $xp->query( "//div[@id='{$id}']/form" );
			foreach( $forms as $form )
				$form->appendChild( $fieldset );
		}
		
		self::$doc->saveHTMLFile( "php://output" );
	}
}
?>
