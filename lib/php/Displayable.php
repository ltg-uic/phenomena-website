<?php
namespace PhenLib;

abstract class Displayable
{
	//TODO - this->id might be more appropriate only on "actions", where it is needed
	protected $id;
	private $className;

	private $doc; //master dom document
	protected $root; //root element to build on

	public function __construct()
	{
		$rootDoc = Template::getDOC();

		$this->doc = $rootDoc->createDocumentFragment();

		$this->root = $rootDoc->createElement( "div" );

		//get the "late static binding" class name
		$this->className = get_called_class();

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

		$this->root->setAttribute( "class", str_replace( "\\", "-", substr( $htmlClass, 0, -1 ) ) ); 
		$this->doc->appendChild( $this->root );
	}

	protected function counter()
	{
		static $x = 0;
		return $x++;
	}

	public function getDOC()
	{
		//don't calculate unique id until output generation, allows for page path to calculate
		$pagePathIdPrefix = str_replace( "/", "_", PageController::getLastPage() );
		$this->id = $pagePathIdPrefix . str_replace( "\\", "-", $this->className ) . "_" . $this->counter();
		$this->root->setAttribute( "id", $this->id );
		$this->generateOutput();
		return $this->doc;
	}

	//require child classes to implement output generation function
	abstract protected function generateOutput();
}
?>
