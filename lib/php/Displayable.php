<?php
namespace PhenLib;

abstract class Displayable
{
	//TODO - this->id might be more appropriate only on "actions", where it is needed
	protected $id;
	private $className;

	private $doc; //master dom document
	protected $root; //root element to build on

	private static $instances = array();

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

		self::$instances[] = $this;
	}

	abstract protected function generateOutput();

	public static function generateAllOutput()
	{
		for( $x=0; $x<sizeof(self::$instances); $x++ )
		{
			$obj = self::$instances[$x];
			//when called this late - last page is the current page
			$pagePathIdPrefix = str_replace( "/", "_", PageController::getLastPage() );
			$obj->id = $pagePathIdPrefix . str_replace( "\\", "-", $obj->className ) . "_" . $obj->counter();
			$obj->root->setAttribute( "id", $obj->id );
			$obj->generateOutput();
		}
		self::$instances = array();
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
