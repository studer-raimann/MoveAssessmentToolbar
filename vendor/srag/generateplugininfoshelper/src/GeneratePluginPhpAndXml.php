<?php

namespace srag\GeneratePluginInfosHelper\SrMoveAssessmentToolbar;

use Closure;
use Composer\Config;
use Composer\Script\Event;
use stdClass;

/**
 * Class GeneratePluginPhpAndXml
 *
 * @package srag\GeneratePluginInfosHelper\SrMoveAssessmentToolbar
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 *
 * @internal
 */
final class GeneratePluginPhpAndXml
{

    const AUTOGENERATED_COMMENT = "Autogenerated from " . self::PLUGIN_COMPOSER_JSON . " - All changes will be overridden if generated again!";
    const LUCENE_OBJECT_DEFINITION_XML = "LuceneObjectDefinition.xml";
    const PLUGIN_COMPOSER_JSON = "composer.json";
    const PLUGIN_PHP = "plugin.php";
    const PLUGIN_README = "README.md";
    const PLUGIN_XML = "plugin.xml";
    /**
     * @var self|null
     */
    private static $instance = null;


    /**
     * GeneratePluginPhpAndXml constructor
     */
    private function __construct()
    {

    }


    /**
     * @param Event $event
     *
     * @internal
     */
    public static function generatePluginPhpAndXml(Event $event)/*: void*/
    {
        $project_root = rtrim(Closure::bind(function () : string {
            return $this->baseDir;
        }, $event->getComposer()->getConfig(), Config::class)(), "/");

        self::getInstance()->doGeneratePluginPhpAndXml($project_root, true, true);
    }


    /**
     * @return self
     */
    public static function getInstance() : self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    /**
     * @param string $project_root
     * @param bool   $autogenerated_comment
     * @param bool   $log
     */
    public function doGeneratePluginPhpAndXml(string $project_root, bool $autogenerated_comment = false, bool $log = false)/*: void*/
    {
        $plugin_composer_json = json_decode(file_get_contents($project_root . "/" . self::PLUGIN_COMPOSER_JSON));

        $this->updateMissingVariablesComposerJson($plugin_composer_json, $project_root, $log);

        $this->generatePluginPhp($plugin_composer_json, $project_root, $autogenerated_comment, $log);

        $this->generatePluginXml($plugin_composer_json, $project_root, $autogenerated_comment, $log);

        $this->generateLuceneObjectDefinitionXml($plugin_composer_json, $project_root, $autogenerated_comment, $log);
    }


    /**
     * @param stdClass $plugin_composer_json
     * @param string   $project_root
     * @param bool     $autogenerated_comment
     * @param bool     $log
     */
    private function generateLuceneObjectDefinitionXml(stdClass $plugin_composer_json, string $project_root, bool $autogenerated_comment, bool $log)/* : void*/
    {
        if (!empty($plugin_composer_json->extra->ilias_plugin->lucene_search)) {
            if ($log) {
                echo "(Re)generate " . self::LUCENE_OBJECT_DEFINITION_XML . "
";
            }

            file_put_contents($project_root . "/" . self::LUCENE_OBJECT_DEFINITION_XML, '<?xml version="1.0" encoding="UTF-8"?>' . ($autogenerated_comment ? '
<!-- ' . htmlspecialchars(self::AUTOGENERATED_COMMENT) . ' -->' : '') . '
<ObjectDefinition xmlns:xi="http://www.w3.org/2001/XInclude" type="' . htmlspecialchars(strval($plugin_composer_json->extra->ilias_plugin->id)) . '">
	<Document type="default">
		<xi:include href="../../../../../../../Services/Object/LuceneDataSource.xml" />
	</Document>
</ObjectDefinition>
');
        }
    }


    /**
     * @param stdClass $plugin_composer_json
     * @param string   $project_root
     * @param bool     $autogenerated_comment
     * @param bool     $log
     */
    private function generatePluginPhp(stdClass $plugin_composer_json, string $project_root, bool $autogenerated_comment, bool $log)/* : void*/
    {
        if ($log) {
            echo "(Re)generate " . self::PLUGIN_PHP . "
";
        }

        $plugins_vars = [
            "id"                => strval($plugin_composer_json->extra->ilias_plugin->id),
            "version"           => strval($plugin_composer_json->version),
            "ilias_min_version" => strval($plugin_composer_json->extra->ilias_plugin->ilias_min_version),
            "ilias_max_version" => strval($plugin_composer_json->extra->ilias_plugin->ilias_max_version),
            "responsible"       => strval($plugin_composer_json->authors[0]->name),
            "responsible_mail"  => strval($plugin_composer_json->authors[0]->email)
        ];

        if (!empty($plugin_composer_json->extra->ilias_plugin->learning_progress)) {
            $plugins_vars["learning_progress"] = true;
        }

        if (!empty($plugin_composer_json->extra->ilias_plugin->supports_export)) {
            $plugins_vars["supports_export"] = true;
        }

        file_put_contents($project_root . "/" . self::PLUGIN_PHP, '<?php' . ($autogenerated_comment ? '
// ' . self::AUTOGENERATED_COMMENT . '
' : '') . '
require_once __DIR__ . "/vendor/autoload.php";

' . implode('
', array_map(function (string $name, $value) : string {
                return '$' . $name . ' = ' . json_encode($value, JSON_UNESCAPED_SLASHES) . ';';
            }, array_keys($plugins_vars), $plugins_vars)) . '
');
    }


    /**
     * @param stdClass $plugin_composer_json
     * @param string   $project_root
     * @param bool     $autogenerated_comment
     * @param bool     $log
     */
    private function generatePluginXml(stdClass $plugin_composer_json, string $project_root, bool $autogenerated_comment, bool $log)/* : void*/
    {
        if ($log) {
            echo "(Re)generate " . self::PLUGIN_XML . "
";
        }

        file_put_contents($project_root . "/" . self::PLUGIN_XML, '<?php xml version = "1.0" encoding = "UTF-8"?>' . ($autogenerated_comment ? '
<!-- ' . htmlspecialchars(self::AUTOGENERATED_COMMENT) . ' -->' : '') . '
<plugin id="' . htmlspecialchars(strval($plugin_composer_json->extra->ilias_plugin->id)) . '">
	' . (!empty($plugin_composer_json->extra->ilias_plugin->events) ? '<events>
		' . implode('
		', array_map(function (stdClass $event) : string {
                    return '<event id="' . htmlspecialchars($event->id) . '" type="' . htmlspecialchars($event->type) . '" />';
                }, (array) $plugin_composer_json->extra->ilias_plugin->events)) . '
	</events>' : '') . '
</plugin>
');
    }


    /**
     * @param string $variable
     * @param string $project_root
     *
     * @return string
     */
    private function getOldPluginVar(string $variable, string $project_root) : string
    {
        if (file_exists($project_root . "/" . self::PLUGIN_PHP)) {
            $plugin_php = file_get_contents($project_root . "/" . self::PLUGIN_PHP);

            $text = [];

            preg_match('/\\$' . $variable . '\\s*=\\s*["\']?([^"\']+)["\']?\\s*;/', $plugin_php, $text);

            if (is_array($text) && count($text) > 1) {
                $text = $text[1];

                if (is_string($text) && !empty($text)) {
                    return $text;
                }
            }
        }

        return "";
    }


    /**
     * @param stdClass $plugin_composer_json
     * @param string   $project_root
     * @param bool     $log
     */
    private function updateMissingVariablesComposerJson(stdClass $plugin_composer_json, string $project_root, bool $log)/* : void*/
    {
        $updated_composer_json = false;

        $old_version = $this->getOldPluginVar("version", $project_root);
        if (empty($plugin_composer_json->version) || (!empty($old_version) && version_compare($old_version, $plugin_composer_json->version, ">"))) {
            if ($log) {
                echo "Update missing or older " . self::PLUGIN_COMPOSER_JSON . " > version (" . ($plugin_composer_json->version ?? null) . ") from " . self::PLUGIN_PHP . " > version ("
                    . $old_version . ")
";
            }

            $plugin_composer_json->version = $old_version;

            $updated_composer_json = true;
        }

        if (empty($plugin_composer_json->extra)) {
            $plugin_composer_json->extra = (object) [];

            $updated_composer_json = true;
        }

        if (isset($plugin_composer_json->ilias_plugin)) {
            if ($log) {
                echo "Migrate " . self::PLUGIN_COMPOSER_JSON . " > ilias_plugin to " . self::PLUGIN_COMPOSER_JSON . " > extra > ilias_plugin
";
            }

            $plugin_composer_json->extra->ilias_plugin = $plugin_composer_json->ilias_plugin;

            unset($plugin_composer_json->ilias_plugin);

            $updated_composer_json = true;
        }

        if (empty($plugin_composer_json->extra->ilias_plugin)) {
            $plugin_composer_json->extra->ilias_plugin = (object) [];

            $updated_composer_json = true;
        }

        $id = $this->getOldPluginVar("id", $project_root);
        if (empty($plugin_composer_json->extra->ilias_plugin->id)) {
            if ($log) {
                echo "Update missing " . self::PLUGIN_COMPOSER_JSON . " > ilias_plugin > id (" . ($plugin_composer_json->extra->ilias_plugin->id ?? null) . ") from " . self::PLUGIN_PHP . " > id ("
                    . $id . ")
";
            }

            $plugin_composer_json->extra->ilias_plugin->id = $id;

            $updated_composer_json = true;
        }

        $name = basename($project_root);
        if (empty($plugin_composer_json->extra->ilias_plugin->name)) {
            if ($log) {
                echo "Update missing " . self::PLUGIN_COMPOSER_JSON . " > ilias_plugin > name (" . ($plugin_composer_json->extra->ilias_plugin->name ?? null) . ") from current folder ("
                    . $name . ")
";
            }

            $plugin_composer_json->extra->ilias_plugin->name = $name;

            $updated_composer_json = true;
        }

        $old_ilias_min_version = $this->getOldPluginVar("ilias_min_version", $project_root);
        if (empty($plugin_composer_json->extra->ilias_plugin->ilias_min_version)
            || (!empty($old_ilias_min_version)
                && version_compare($old_ilias_min_version, $plugin_composer_json->extra->ilias_plugin->ilias_min_version, ">"))
        ) {
            if ($log) {
                echo "Update missing or older " . self::PLUGIN_COMPOSER_JSON . " > ilias_plugin > ilias_min_version (" . ($plugin_composer_json->extra->ilias_plugin->ilias_min_version ?? null)
                    . ") from " . self::PLUGIN_PHP . " > ilias_min_version ("
                    . $old_ilias_min_version . ")
";
            }

            $plugin_composer_json->extra->ilias_plugin->ilias_min_version = $old_ilias_min_version;

            $updated_composer_json = true;
        }

        $old_ilias_max_version = $this->getOldPluginVar("ilias_max_version", $project_root);
        if (empty($plugin_composer_json->extra->ilias_plugin->ilias_max_version)
            || (!empty($old_ilias_max_version)
                && version_compare($old_ilias_max_version, $plugin_composer_json->extra->ilias_plugin->ilias_max_version, ">"))
        ) {
            if ($log) {
                echo "Update missing or older " . self::PLUGIN_COMPOSER_JSON . " > ilias_plugin > ilias_max_version (" . ($plugin_composer_json->extra->ilias_plugin->ilias_max_version ?? null)
                    . ") from " . self::PLUGIN_PHP . " > ilias_max_version ("
                    . $old_ilias_max_version . ")
";
            }

            $plugin_composer_json->extra->ilias_plugin->ilias_max_version = $old_ilias_max_version;

            $updated_composer_json = true;
        }

        if (empty($plugin_composer_json->extra->ilias_plugin->slot)) {
            $plugin_class = "classes/class.il" . $plugin_composer_json->extra->ilias_plugin->name . "Plugin.php";

            $plugin_class_code = file_get_contents($project_root . "/" . $plugin_class);

            $matches = [];
            preg_match("/Plugin\s+extends\s+il([A-Za-z]+)Plugin/", $plugin_class_code, $matches);
            $hook = $matches[1];

            $matches = [];
            $readme = file_get_contents($project_root . "/" . self::PLUGIN_README);
            preg_match("/Customizing\/global\/plugins\/([A-Za-z]+)\/([A-Za-z]+)\/" . $hook . "/", $readme, $matches);

            $component = implode("/", [
                $matches[1],
                $matches[2]
            ]);

            $slot = implode("/", [
                $component,
                $hook
            ]);

            if ($log) {
                echo "Update missing " . self::PLUGIN_COMPOSER_JSON . " > ilias_plugin > slot (" . ($plugin_composer_json->extra->ilias_plugin->slot ?? null) . ") from " . $plugin_class . " ("
                    . $hook
                    . ") and "
                    . self::PLUGIN_README . " ("
                    . $component . ")
";
            }

            $plugin_composer_json->extra->ilias_plugin->slot = $slot;

            $updated_composer_json = true;
        }

        if (empty($plugin_composer_json->extra->ilias_plugin->learning_progress)) {
            $learning_progress = $this->getOldPluginVar("learning_progress", $project_root);

            if ($learning_progress === "true") {
                if ($log) {
                    echo "Update missing " . self::PLUGIN_COMPOSER_JSON . " > ilias_plugin > learning_progress (" . ($plugin_composer_json->extra->ilias_plugin->learning_progress ?? null)
                        . ") from "
                        . self::PLUGIN_PHP . " > learning_progress (" . $learning_progress . ")
";
                }

                $plugin_composer_json->extra->ilias_plugin->learning_progress = true;

                $updated_composer_json = true;
            }
        }

        if (empty($plugin_composer_json->extra->ilias_plugin->lucene_search)) {
            $lucene_search = json_encode(file_exists($project_root . "/" . self::LUCENE_OBJECT_DEFINITION_XML));

            if ($lucene_search === "true") {
                if ($log) {
                    echo "Update missing " . self::PLUGIN_COMPOSER_JSON . " > ilias_plugin > lucene_search (" . ($plugin_composer_json->extra->ilias_plugin->lucene_search ?? null) . ") from "
                        . self::LUCENE_OBJECT_DEFINITION_XML . " (" . $lucene_search . ")
";
                }

                $plugin_composer_json->extra->ilias_plugin->lucene_search = true;

                $updated_composer_json = true;
            }
        }

        if (empty($plugin_composer_json->extra->ilias_plugin->supports_export)) {
            $supports_export = $this->getOldPluginVar("supports_export", $project_root);

            if ($supports_export === "true") {
                if ($log) {
                    echo "Update missing " . self::PLUGIN_COMPOSER_JSON . " > ilias_plugin > supports_export (" . ($plugin_composer_json->extra->ilias_plugin->supports_export ?? null) . ") from "
                        . self::PLUGIN_PHP . " > supports_export (" . $supports_export . ")
";
                }

                $plugin_composer_json->extra->ilias_plugin->supports_export = true;

                $updated_composer_json = true;
            }
        }

        if (empty($plugin_composer_json->authors)) {
            $responsible = $this->getOldPluginVar("responsible", $project_root);
            $responsible_mail = $this->getOldPluginVar("responsible_mail", $project_root);

            if ($log) {
                echo "Update missing " . self::PLUGIN_COMPOSER_JSON . " > authors (" . ($plugin_composer_json->authors ?? null) . ") from " . self::PLUGIN_PHP . " > responsible (" . $responsible
                    . ") and " . self::PLUGIN_PHP . " > responsible_mail ("
                    . $responsible_mail . ")
";
            }

            $plugin_composer_json->authors = [
                (object) [
                    "name"     => $responsible,
                    "email"    => $responsible_mail,
                    "homepage" => "",
                    "role"     => "Developer"
                ]
            ];

            $updated_composer_json = true;
        }

        if (empty($plugin_composer_json->extra->ilias_plugin->events)) {
            if (file_exists($project_root . "/" . self::PLUGIN_XML)) {
                $plugin_xml = json_decode(json_encode(simpleXML_load_file($project_root . "/" . self::PLUGIN_XML)));

                if (!empty($plugin_xml->events) && !empty($plugin_xml->events->event)) {
                    if ($log) {
                        echo "Update missing " . self::PLUGIN_COMPOSER_JSON . " > ilias_plugin > events (" . ($plugin_composer_json->extra->ilias_plugin->events ?? null) . ") from "
                            . self::PLUGIN_XML . " > events
";
                    }

                    $plugin_composer_json->extra->ilias_plugin->events = array_map(function (stdClass $event) : stdClass {
                        return (object) [
                            "id"   => $event->{"@attributes"}->id,
                            "type" => $event->{"@attributes"}->type
                        ];
                    }, $plugin_xml->events->event);

                    $updated_composer_json = true;
                }
            }
        }

        if ($updated_composer_json) {
            if ($log) {
                echo "Store updated changes in " . self::PLUGIN_COMPOSER_JSON . "
";
            }

            file_put_contents($project_root . "/" . self::PLUGIN_COMPOSER_JSON, preg_replace_callback("/\n( +)/", function (array $matches) : string {
                    return "
" . str_repeat(" ", (strlen($matches[1]) / 2));
                }, json_encode($plugin_composer_json, JSON_UNESCAPED_SLASHES + JSON_PRETTY_PRINT)) . "
");
        }
    }
}
