<?php
namespace PhenLib;

abstract class Displayable
{
	//TODO - this->id might be more appropriate only on "actions", where it is needed
	//     - or as a common ancestor, like "component" or "resource"
	//     - consider initing id to null and add some late check that throws exception if executing and it's not yet set
	protected $id;
	private $className;

	private $doc; //master dom document
	protected $root; //root element to build on

	public function __construct()
	{
		//==== INIT VARS ====
		$this->id = NULL;

		//get the "late static binding" class name;
		$this->className = get_called_class();

		$rootDoc = Template::getDOC();
		$this->doc = $rootDoc->createDocumentFragment();
		$this->root = $rootDoc->createElement( "div" );

		//==== GENERATE HTML CLASSES BASED ON PHP CLASS RELATIONS ====
		//TODO	- this might be overkill, and possibly ineficient, but lets go for it for now:
		//	- need to filter for duplicate interfaces / traits in the final string
		$classReflect = new \ReflectionClass( $this->className ); 
		$htmlClass = "";
		do
		{
			$htmlClass .= $classReflect->getName() . " ";

			foreach( $classReflect->getInterfaceNames() as $interface )
				$htmlClass .= $interface . " ";

			foreach( $classReflect->getTraitNames() as $trait )
				$htmlClass .= $trait . " ";
		} while( $classReflect = $classReflect->getParentClass() );


		//==== BUILD AND APPEND WRAPPER DIV ====
		$this->root->setAttribute( "class", str_replace( "\\", "-", substr( $htmlClass, 0, -1 ) ) ); 
		$this->doc->appendChild( $this->root );
	}

	protected function counter()
	{
		static $x = 0;
		return $x++;
	}

	//manually set id based on posted id, in case executing before generating display, id numbering
	public function setID( $id )
	{
		//surround with try to catch array / index errrors
		try
		{
			if( $this->className !== str_replace( "-", "\\", explode( "__", $id )[1] ) )
				throw new \Exception();
		}
		catch( \Exception $e )
		{
			throw new \Exception( "Invalid ID (class name mismatch) supplied to Displayable: {$this->className}" );	
		}
		$this->__setID( $id );
	}

	private function __setID( $id )
	{
		$this->id = $id;
		$this->root->setAttribute( "id", $this->id );
	}

	public function getID()
	{
		return $this->id;
	}

	public function getDOC()
	{
		//don't calculate unique id until output generation, allows for page path to calculate
		//html4 format for id only allows [A-Za-z][-A-Za-z0-9_:.]*
		$this->__setID( str_replace( "/", "_", substr( PageController::getLastPage(), 0, -1 ) ) . "__" .
				str_replace( "\\", "-", $this->className ) . "__" . $this->counter() . "__" );
		$this->generateOutput();
		return $this->doc;
	}

	//require child classes to implement output generation function
	abstract protected function generateOutput();
}
?>
