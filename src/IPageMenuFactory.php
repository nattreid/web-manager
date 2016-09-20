<?php

namespace NAttreid\WebManager;

use NAttreid\Menu\Menu;


interface IPageMenuFactory
{
	/**  @return Menu */
	public function create();
}