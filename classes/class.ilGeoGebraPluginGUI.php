<?php
declare(strict_types=1);
/**
 * Disclaimer: This file is part of the GeoGebra Repository Object plugin for ILIAS.
 */

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use platform\GeoGebraConfig;
use platform\GeoGebraException;

/**
 * Class ilObjGeoGebraGUI
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 * @ilCtrl_isCalledBy ilGeoGebraPluginGUI: ilPCPluggedGUI
 * @ilCtrl_Calls      ilGeoGebraPluginGUI: ilObjRootFolderGUI
 */
class ilGeoGebraPluginGUI extends ilPageComponentPluginGUI
{
    private Factory $factory;
    private Renderer $renderer;
    private ilCtrlInterface $ctrl;
    private ilGlobalTemplateInterface $tpl;
    private ilTabsGUI $tabs;
    private UploadServiceGUI $uploader;

    protected static int $id_counter = 0;

    public function __construct()
    {
        parent::__construct();

        global $DIC;

        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tabs = $DIC->tabs();
        $this->uploader = new UploadServiceGUI();
    }

    public function executeCommand(): void
    {
        global $DIC;

        $cmd = $DIC->ctrl()->getCmd();

        $this->{$cmd}();
    }

    /**
     * @throws ilCtrlException
     */
    public function insert(): void
    {
        $this->ctrl->redirect($this, 'create');
    }

    /**
     * @throws ilCtrlException
     */
    public function edit(): void
    {
        $this->setSubTabs("subtab_generic_settings");

        $properties = $this->getProperties();

        if (isset($properties["fileName"])) {
            $old_path = ILIAS_WEB_DIR . "/" . CLIENT_ID . "/geogebra/" . $properties["fileName"];

            if (file_exists($old_path)) {
                $this->moveToIRSS($old_path);
                return;
            }
        }

        $form = $this->factory->input()->container()->form()->standard(
            $this->ctrl->getLinkTarget($this, "update"),
            $this->buildForm($properties)
        );

        $this->tpl->setContent($this->renderer->render($form));
    }

    /**
     * @throws ilCtrlException
     */
    public function editAdvanced(): void
    {
        $this->setSubTabs("subtab_advanced_settings");

        $form = $this->factory->input()->container()->form()->standard(
            $this->ctrl->getLinkTarget($this, "updateAdvanced"),
            $this->buildFormAdvanced($this->getProperties())
        );

        $this->tpl->setContent($this->renderer->render($form));
    }

    /**
     * @throws ilCtrlException
     * @throws GeoGebraException
     */
    public function create(): void
    {
        global $DIC;

        $request = $DIC->http()->request();

        $form = $this->factory->input()->container()->form()->standard(
            "#",
            $this->buildForm()
        );

        if ($request->getMethod() == "POST") {
            $form = $form->withRequest($request);

            $result = $form->getData();

            if ($result) {
                $properties = [
                    "title" => $result["title"],
                    "legacyFileName" => $this->uploader->getInfoResult($result["file"][0])->getName(),
                    "fileName"       => $result["file"][0]
                ];

                $properties = $this->mergeCustomSettings($properties, $result);

                $properties = $this->mergeAdvancedSettings($properties, $result);

                $this->createElement($properties);
                $this->returnToParent();
            }
        }

        $this->tpl->setContent($this->renderer->render($form));
    }

    /**
     * @throws GeoGebraException
     */
    public function update()
    {
        global $DIC;

        $request = $DIC->http()->request();

        $form = $this->factory->input()->container()->form()->standard(
            "#",
            $this->buildForm()
        );

        if ($request->getMethod() == "POST") {
            $form = $form->withRequest($request);

            $result = $form->getData();

            if ($result) {
                $properties = $this->getProperties();

                $properties["title"] = $result["title"];
                $properties["legacyFileName"] = $this->uploader->getInfoResult($result["file"][0])->getName();
                $properties["fileName"] = $result["file"][0];

                $properties = $this->mergeCustomSettings($properties, $result);

                $this->updateElement($properties);
                $this->returnToParent();
            }
        }

        $this->tpl->setContent($this->renderer->render($form));
    }

    /**
     * @throws GeoGebraException
     */
    public function updateAdvanced()
    {
        global $DIC;

        $request = $DIC->http()->request();

        $form = $this->factory->input()->container()->form()->standard(
            "#",
            $this->buildFormAdvanced()
        );

        if ($request->getMethod() == "POST") {
            $form = $form->withRequest($request);

            $result = $form->getData();

            if ($result) {
                $properties = $this->getProperties();

                $properties = $this->mergeAdvancedSettings($properties, $result);

                $this->updateElement($properties);
                $this->returnToParent();
            }
        }

        $this->tpl->setContent($this->renderer->render($form));
    }

    protected function convertValueByType(string $type, $value) {
        if ($type === "numeric") {
            if (is_int($value)) {
                return intval($value);
            } else {
                return doubleval($value);
            }
        } else if ($type === "checkbox") {
            return boolval($value);
        }

        return $value;
    }


    protected function fetchCustomFieldTypes(string $field_name): string
    {
        switch ($field_name) {
            case "width":
            case "height":
                return "numeric";
                break;
            case "enableShiftDragZoom":
            case "showResetIcon":
                return "checkbox";
                break;
        }

        return "text";
    }

    protected function convertPropertyValueTypes(&$properties) {
        foreach ($properties as $key => $property) {
            if (strpos($key, "custom_") == 0) {
                $postKey = str_replace("custom_", "", $key);
                $field_type = $this->fetchCustomFieldTypes($postKey);
                $properties[$key] = $this->convertValueByType($field_type, $property);
            }

            if (strpos($key, "advanced_") == 0) {
                $postKey = str_replace("advanced_", "", $key);
                if (isset($this->getAdvancedInputs()[$postKey][0])) {
                    $field_type = $this->getAdvancedInputs()[$postKey][0];
                    $properties[$key] = $this->convertValueByType($field_type, $property);
                }
            }}
    }

    protected function calculateScalingHeight(&$properties_after_change): string
    {
        if (isset($properties_after_change["custom_height"])) {
            $scaling_height = (float) $properties_after_change["custom_height"];

            if (isset($properties_after_change["advanced_scale"])) {
                $scale_factor = (float) $properties_after_change["advanced_scale"];

                if ($scale_factor < floatval(1)) {
                    $properties_after_change["custom_alignment"] = "left";

                    $scaling_height *= $scale_factor;
                }
            }

            return $scaling_height . "px";
        } else {
            return "800px";
        }
    }

    /**
     * @throws ilTemplateException
     */
    public function getElementHTML(string $a_mode, array $a_properties, string $plugin_version): string
    {
        global $DIC;

        self::$id_counter++;
        $id = "srgg_" . self::$id_counter;

        $scale_height = $this->calculateScalingHeight($a_properties);

        $old_path = ILIAS_WEB_DIR . '/' . CLIENT_ID . "/geogebra/" . $a_properties["fileName"];

        if (!file_exists($old_path)) {
            $irss = $DIC->resourceStorage();
            $file_name = $irss->consume()->src(new ResourceIdentification($a_properties["fileName"]))->getSrc();
        } else {
            $file_name = $old_path;
        }

        if (!empty($iframe_id = filter_input(INPUT_GET, "iframe"))) {
            if ($iframe_id === $id) {
                $tpl = new ilTemplate("tpl.geogebra_iframe.html", true, true, "Customizing/global/plugins/Services/COPage/PageComponent/GeoGebra");

                $tpl->setVariable("ID", $id);

                $tpl->setVariable("SCALE_WRAPPER_HEIGHT", $scale_height);

                $this->convertPropertyValueTypes($a_properties);

                $raw_alignment = $a_properties["custom_alignment"] ?? "left";
                $alignment = empty($raw_alignment) ? "left" : $raw_alignment;
                $tpl->setVariable("ALIGNMENT", $alignment);

                $tpl->setVariable("TEMPLATES_DIR", "Customizing/global/plugins/Services/COPage/PageComponent/GeoGebra/templates");
                $tpl->setVariable("PLUGIN_DIR", "Customizing/global/plugins/Services/COPage/PageComponent/GeoGebra");
                $tpl->setVariable("FILE_NAME", $file_name);

                $tpl->setVariable("PROPERTIES", json_encode($a_properties));

                echo $tpl->get();
                die;
            } else {
                return " ";
            }
        } else {
            $tpl = new ilTemplate("tpl.geogebra.html", true, true, "Customizing/global/plugins/Services/COPage/PageComponent/GeoGebra");


            $tpl->setVariable("ID", $id);

            if (isset($_SERVER["REQUEST_URI"])) {
                $tpl->setVariable("URL", filter_input(INPUT_SERVER, "REQUEST_URI") . '&iframe=' . $id);
            }

            $tpl->setVariable("SCALE_WRAPPER_HEIGHT", $scale_height);

            return $tpl->get();
        }
    }

    /**
     * @throws ilCtrlException
     */
    protected function setSubTabs(string $active) {
        $this->tabs->addSubTab(
            "subtab_generic_settings",
            $this->plugin->txt("subtab_generic_settings"),
            $this->ctrl->getLinkTarget($this, "edit")
        );

        $this->tabs->addSubTab(
            "subtab_advanced_settings",
            $this->plugin->txt("subtab_advanced_settings"),
            $this->ctrl->getLinkTarget($this, "editAdvanced")
        );

        $this->tabs->activateSubTab($active);
    }

    protected function renderForm(string $form_action, array $sections): string
    {
        $form = $this->factory->input()->container()->form()->standard(
            $form_action,
            $sections
        );

        return $this->renderer->render($form);
    }

    private function buildForm(?array $properties = null): array
    {
        $inputs = array();

        $inputs["title"] = $this->factory->input()->field()->text($this->plugin->txt("component_title"))
            ->withRequired(true)->withValue($properties["title"] ?? "");



        $inputs["file"] = $this->factory->input()->field()->file($this->uploader, $this->plugin->txt("component_geogebra_file"))
            ->withRequired(true);

        if (isset($properties["fileName"])) {
            $inputs["file"] = $inputs["file"]->withValue(array(
                0 => $properties["fileName"]
            ));
        }

        $inputs["width"] = $this->factory->input()->field()->numeric($this->plugin->txt("component_width"))
            ->withRequired(true)->withValue($properties["custom_width"] ?? 800);

        $inputs["height"] = $this->factory->input()->field()->numeric($this->plugin->txt("component_height"))
            ->withRequired(true)->withValue($properties["custom_height"] ?? 600);

        $inputs["enableShiftDragZoom"] = $this->factory->input()->field()->checkbox($this->plugin->txt("component_enableShiftDragZoom"))
            ->withValue(isset($properties["custom_enableShiftDragZoom"]) && (bool) $properties["custom_enableShiftDragZoom"]);

        $inputs["showResetIcon"] = $this->factory->input()->field()->checkbox($this->plugin->txt("component_showResetIcon"))
            ->withValue(isset($properties["custom_showResetIcon"]) && (bool) $properties["custom_showResetIcon"]);

        $inputs["alignment"] = $this->factory->input()->field()->select($this->plugin->txt("component_aligment"), [
            "left" => $this->plugin->txt("component_left"),
            "center" => $this->plugin->txt("component_center"),
            "right" => $this->plugin->txt("component_right")
        ])->withValue($properties["custom_alignment"] ?? "left");


        return $inputs;
    }

    private function buildFormAdvanced(?array $properties = null): array
    {
        $inputs = $this->getAdvancedInputs();

        foreach ($inputs as $key => $input) {
            $value = $properties["advanced_" . $key] ?? "";

            switch ($input[0]) {
                case "numeric":
                    $inputs[$key] = $this->factory->input()->field()->numeric($this->plugin->txt('config_' . $key))
                        ->withValue($value != "" ? $value : $input[1]);
                    break;
                case "checkbox":
                    $inputs[$key] = $this->factory->input()->field()->checkbox($this->plugin->txt('config_' . $key))
                        ->withValue($value == "true" ?? $input[1]);
                    break;
                case "select":
                    $inputs[$key] = $this->factory->input()->field()->select($this->plugin->txt('config_' . $key), $input[1]);

                    if ($value != "") {
                        $inputs[$key] = $inputs[$key]->withValue($value);
                    }

                    break;
                case "color":
                    $inputs[$key] = $this->factory->input()->field()->text($this->plugin->txt('config_' . $key))
                        ->withValue($value != "" ? (string) $value : $input[1])->withOnLoadCode(function ($id) {
                            return "$('#$id').attr('type', 'color').width('50px');";
                        });
                    break;
                case "text":
                    $inputs[$key] = $this->factory->input()->field()->text($this->plugin->txt('config_' . $key))
                        ->withValue($value != "" ? (string) $value : $input[1]);
                    break;
            }
        }

        return $inputs;
    }

    private function getAdvancedInputs(): array
    {
        return array(
            "appName" => ["select", [
                "classic" => "Classic", "graphing" => "Graphing", "geometry" => "Geometry", "3d" => "3D"
            ]],
            "borderColor" => ["color", "#FFFFFF"],
            "enableRightClick" => ["checkbox", false],
            "enableLabelDrags" => ["checkbox", false],
            "showZoomButtons" => ["checkbox", false],
            "errorDialogsActive" => ["checkbox", false],
            "showMenuBar" => ["checkbox", false],
            "showToolBar" => ["checkbox", false],
            "showToolBarHelp" => ["checkbox", false],
            "showAlgebraInput" => ["checkbox", false],
            "language" => ["text", "en"],
            "allowStyleBar" => ["checkbox", false],
            "useBrowserForJS" => ["checkbox", false],
            "showLogging" => ["checkbox", false],
            "capturingThreshold" => ["numeric", 10],
            "enable3d" => ["checkbox", false],
            "enableCAS" => ["checkbox", false],
            "algebraInputPosition" => ["text", ""],
            "preventFocus" => ["checkbox", false],
            "autoHeight" => ["checkbox", false],
            "allowUpscale" => ["checkbox", false],
            "playButton" => ["checkbox", false],
            "scale" => ["numeric", 1],
            "showAnimationButton" => ["checkbox", false],
            "showFullscreenButton" => ["checkbox", false],
            "showSuggestionButtons" => ["checkbox", false],
            "showStartTooltip" => ["checkbox", false],
            "rounding" => ["text", ""],
            "buttonShadows" => ["checkbox", false],
            "buttonRounding" => ["text", "0.2"],
        );
    }

    /**
     * @throws GeoGebraException
     */
    protected function mergeCustomSettings(&$properties, array $result): array
    {
        GeoGebraConfig::load();
        $immutable_fields = GeoGebraConfig::get("immutable");
        $allSettings = GeoGebraConfig::getAll();
        $formatedCustomSettings = [];

        if (!is_array($immutable_fields)) {
            $immutable_fields = array();
        }

        foreach ($allSettings as $key => $value) {
            $key = str_replace("default_", "", $key);

            if (isset($result[$key])) {
                if (in_array($key, $immutable_fields)) {
                    $formatedCustomSettings["custom_" . $key] = $value;
                } else {
                    $formatedCustomSettings["custom_" . $key] = $result[$key];
                }

                unset($result[$key]);
            }
        }

        foreach ($result as $key => $value) {
            if (strpos($key, "file") === false && strpos($key, "title") === false) {
                $formatedCustomSettings["custom_" . $key] = $value;
            }
        }

        return array_merge($properties, $formatedCustomSettings);
    }


    /**
     * @throws GeoGebraException
     */
    protected function mergeAdvancedSettings(&$properties, array $result): array
    {
        GeoGebraConfig::load();
        $allSettings = GeoGebraConfig::getAll();
        $advancedSettings = [];

        foreach ($allSettings as $key => $occurring_value) {
            if ($key !== "immutable" && strpos($key, "default_") !== 0) {
                $advancedSettings["advanced_" . $key] = $result[$key] ?? $occurring_value;
            }
        }

        return array_merge($properties, $advancedSettings);
    }

    /**
     * @throws ilCtrlException
     */
    private function moveToIRSS(string $file): void
    {
        $form = $this->factory->input()->container()->form()->standard(
            $this->ctrl->getLinkTarget($this, "submitMoveToIRSS"),
            [
                "file" => $this->factory->input()->field()->file($this->uploader, $this->plugin->txt("component_geogebra_file"))->withRequired(true)->withOnLoadCode(function ($id) use ($file) {
                    return $this->donwloadCode($id, $file);
                })
            ]
        );

        $disclaimer = $this->factory->messageBox()->info($this->plugin->txt("disclaimer_reupload_file"));

        $this->tpl->setContent($this->renderer->render([$disclaimer, $form]));
    }

    private function submitMoveToIRSS()
    {
        global $DIC;

        $request = $DIC->http()->request();

        $form = $this->factory->input()->container()->form()->standard(
            "#",
            [
                "file" => $this->factory->input()->field()->file($this->uploader, $this->plugin->txt("component_geogebra_file"))->withRequired(true)
            ]
        );

        if ($request->getMethod() == "POST") {
            $form = $form->withRequest($request);

            $result = $form->getData();

            if ($result) {
                $properties = $this->getProperties();

                $properties["legacyFileName"] = $this->uploader->getInfoResult($result["file"][0])->getName();
                $properties["fileName"] = $result["file"][0];

                $this->updateElement($properties);
                $this->returnToParent();
            }
        }

        $disclaimer = $this->factory->messageBox()->info($this->plugin->txt("disclaimer_reupload_file"));

        $this->tpl->setContent($this->renderer->render([$disclaimer, $form]));
    }

    private function donwloadCode($id, string $file): string
    {
        return <<<JS
            // Crear un enlace invisible para descargar el archivo
            var link = document.createElement('a');
            link.href = '$file'; // URL del archivo que quieres descargar
            link.download = '$file'.split('/').pop(); // Nombre del archivo a descargar
            document.body.appendChild(link);
            
            // Forzar la descarga
            link.click();
            
            // Eliminar el enlace después de la descarga
            document.body.removeChild(link);
        JS;
    }
}
