<?php

namespace srag\DIC\MoveAssessmentToolbar;

use srag\DIC\MoveAssessmentToolbar\DIC\DICInterface;
use srag\DIC\MoveAssessmentToolbar\Exception\DICException;
use srag\DIC\MoveAssessmentToolbar\Output\OutputInterface;
use srag\DIC\MoveAssessmentToolbar\Plugin\PluginInterface;
use srag\DIC\MoveAssessmentToolbar\Version\VersionInterface;

/**
 * Interface DICStaticInterface
 *
 * @package srag\DIC\MoveAssessmentToolbar
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface DICStaticInterface {

	/**
	 * Clear cache. Needed for instance in unit tests
	 */
	public static function clearCache()/*: void*/
	;


	/**
	 * Get DIC interface
	 *
	 * @return DICInterface DIC interface
	 */
	public static function dic()/*: DICInterface*/
	;


	/**
	 * Get output interface
	 *
	 * @return OutputInterface Output interface
	 */
	public static function output()/*: OutputInterface*/
	;


	/**
	 * Get plugin interface
	 *
	 * @param string $plugin_class_name
	 *
	 * @return PluginInterface Plugin interface
	 *
	 * @throws DICException Class $plugin_class_name not exists!
	 * @throws DICException Class $plugin_class_name not extends ilPlugin!
	 * @logs   DEBUG Please implement $plugin_class_name::getInstance()!
	 */
	public static function plugin(/*string*/
		$plugin_class_name)/*: PluginInterface*/
	;


	/**
	 * Get version interface
	 *
	 * @return VersionInterface Version interface
	 */
	public static function version()/*: VersionInterface*/
	;
}
