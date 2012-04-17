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
		$doc = self::$doc = $di->createDocument( NULL, "html", $di->createDocumentType(
			"html", "-//W3C//DTD XHTML 1.0 Strict//EN", "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" ) );
		$doc->xmlVersion = "1.0";
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

	static public function integrate( $hook, $obj )
	{
		self::init();

		if( ! $obj instanceof Displayable )
			throw new \Exception( "Template cannot integrate non-pluggable object" );

		self::$hooks[$hook]->appendChild( $obj->getDOC() );
	
		//TODO - LOGIC FOR ADDING NEW HOOKS TO THE TEMPLATE FROM A DISPLAYABLE
//		if( is_array( ( $h = $obj->getHooks() ) ) )
//			self::$hooks[] = $obj->getHooks();
	}

	static public function appendDOM( $hook, $dom )
	{
		self::init();

		self::$hooks[$hook]->appendChild( $dom );
	}

	static public function HTMLtoDOM( $html )
	{
		self::init();

		$frag = self::$doc->createDocumentFragment();
		$frag->appendXML( $html );
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

			$forms = $xp->query( "//div[@id='{$id}']/form" );
			foreach( $forms as $form )
				$form->appendChild( $input );
		}
		

		self::$doc->save( "php://output" );
	}
}
?>
