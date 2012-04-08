<?php
namespace PhenLib;

abstract class Displayable
{
	//TODO - this->id might be more appropriate only on "actions", where it is needed
	protected $id;

	private $doc; //master dom document
	protected $root; //root element to build on

	protected function __construct()
	{
		$rootDoc = Template::getDOC();

		$this->doc = $rootDoc->createDocumentFragment();

		$this->root = $rootDoc->createElement( "div" );

		//get the "late static binding" class name
		$className = get_called_class();

		$this->id = str_replace( "\\", "-", $className ) . "_" . $this->counter();

		$this->root->setAttribute( "id", $this->id );

		//TODO	- this might be overkill, and possibly ineficient, but lets go for it for now:
		//	- need to filter for duplicate interfaces / traits in the final string
		$classReflect = new \ReflectionClass( $className ); 
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
		return $this->doc;
	}
}
?>
