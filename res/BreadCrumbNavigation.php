<?php
namespace Phen;

class BreadCrumbNavigation extends \PhenLib\Displayable
{
	public function __construct()
	{
		parent::__construct();

		$rootDoc = \PhenLib\Template::getDOC();
		$root = $this->root;
		
		$rq = \PhenLib\PageController::getResourceQueue();
		$rqc = $rq->count();
		
		//breadcrumb navigation
		$prefix = "";
		for( $x=0; $x<$rqc-1; $x++ )
		        $prefix .= "../";
		
		$rq->rewind();
			$title = "<a href=\"{$prefix}\">" . $rq->current()->getTitle() . "</a>";
		$rq->next();
		while( $rq->valid() )
		{
		        $prefix = substr( $prefix, 3 );
		        $title .= " - " . "<a href=\"{$prefix}\">" . $rq->current()->getTitle() . "</a>";
		        $rq->next();
		}

		//replaces root container
		$root->parentNode->replaceChild( \PhenLib\Template::HTMLtoDOM( "<h1>{$title}</h1>" ), $root );
	}

}
?>
