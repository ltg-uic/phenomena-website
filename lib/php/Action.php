<?php
namespace PhenLib;

interface Action
{
	public function execute();
	public function getRedirect();
}
?>
