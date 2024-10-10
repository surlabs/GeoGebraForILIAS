<?php
declare(strict_types=1);
/**
 * Disclaimer: This file is part of the GeoGebra Repository Object plugin for ILIAS.
 */

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use platform\GeoGebraConfig;
use platform\GeoGebraException;

/**
 * Class ilGeoGebraConfigGUI
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 * @ilCtrl_IsCalledBy  ilGeoGebraConfigGUI: ilObjComponentSettingsGUI
 */
class ilGeoGebraConfigGUI extends ilPluginConfigGUI
{
    private ilGeoGebraPlugin $plugin;
    private Factory $factory;
    private Renderer $renderer;
    private \ILIAS\Refinery\Factory $refinery;
    private ilCtrlInterface $ctrl;
    private ilGlobalTemplateInterface $tpl;
    private $request;
    private ilTabsGUI $tabs;
    private array $immutableFields = [];

    public function __construct()
    {
        global $DIC;
        $this->plugin = ilGeoGebraPlugin::getInstance();
        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->refinery = $DIC->refinery();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->request = $DIC->http()->request();
        $this->tabs = $DIC->tabs();
    }

    public function performCommand(string $cmd): void
    {
        $this->{$cmd}();
    }

    /**
     * @throws ilCtrlException
     */
    private function configure(): void
    {
        $this->configureDefaultValues();
    }

    /**
     * @throws ilCtrlException
     */
    private function initTabs()
    {
        $this->tabs->addTab('default_values', $this->plugin->txt('config_default_values'), $this->ctrl->getLinkTarget($this, 'configureDefaultValues'));
        $this->tabs->addTab('immutables', $this->plugin->txt('config_immutables'), $this->ctrl->getLinkTarget($this, 'configureImmutables'));
    }

    /**
     * @throws ilCtrlException
     */
    private function configureDefaultValues()
    {
        $this->initTabs();

        $this->tabs->activateTab('default_values');

        $this->tpl->setContent($this->renderForm($this->ctrl->getLinkTargetByClass(ilGeoGebraConfigGUI::class, 'configureDefaultValues'), $this->buildDefaultValuesForm(), "default_values"));
    }

    /**
     * @throws ilCtrlException
     * @throws GeoGebraException
     */
    private function configureImmutables()
    {
        $this->immutableFields = is_array(GeoGebraConfig::get("immutable")) ? GeoGebraConfig::get("immutable") : [];

        $this->initTabs();

        $this->tabs->activateTab('immutables');

        $this->tpl->setContent($this->renderForm($this->ctrl->getLinkTargetByClass(ilGeoGebraConfigGUI::class, 'configureImmutables'), $this->buildImmutablesForm(), "immutable"));
    }

    private function renderForm(string $form_action, array $sections, string $type): string
    {
        $form = $this->factory->input()->container()->form()->standard(
            $form_action,
            $sections
        );

        $saving_info = "";

        if ($this->request->getMethod() == "POST") {
            $form = $form->withRequest($this->request);
            $result = $form->getData();
            if ($result) {
                $saving_info = $this->save($type);

                $form = $this->factory->input()->container()->form()->standard(
                    $form_action,
                    $type == "default_values" ? $this->buildDefaultValuesForm() : $this->buildImmutablesForm()
                );
            }
        }

        return $saving_info . $this->renderer->render($form);
    }

    /**
     * @throws GeoGebraException
     */
    private function buildDefaultValuesForm(): array
    {
        $inputs = $this->getInputs();

        foreach ($inputs as $key => $input) {
            $value = GeoGebraConfig::get($key);

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

            $inputs[$key] = $inputs[$key]->withAdditionalTransformation($this->refinery->custom()->transformation(
                function ($v) use ($key) {
                    GeoGebraConfig::set($key, $v);
                }
            ));
        }

        return $inputs;
    }

    private function buildImmutablesForm(): array
    {
        $inputs = $this->getInputs();

        foreach ($inputs as $key => $input) {
            $inputs[$key] = $this->factory->input()->field()->checkbox($this->plugin->txt('config_' . $key))
                ->withValue(in_array($key, $this->immutableFields))
                ->withAdditionalTransformation($this->refinery->custom()->transformation(
                    function ($v) use ($key) {
                        if ($v) {
                            $this->immutableFields[] = $key;
                        } else {
                            $this->immutableFields = array_diff($this->immutableFields, [$key]);
                        }
                    }
                ));
        }

        return $inputs;
    }

    private function save(string $type): string
    {
        if ($type == "immutable") {
            GeoGebraConfig::set('immutable', $this->immutableFields);
        }

        GeoGebraConfig::save();
        return $this->renderer->render($this->factory->messageBox()->success($this->plugin_object->txt('config_msg_success')));
    }

    private function getInputs(): array
    {
        return array(
            "default_width" => ["numeric", 800],
            "default_height" => ["numeric", 600],
            "default_enableShiftDragZoom" => ["checkbox", false],
            "default_showResetIcon" => ["checkbox", false],
            "default_alignment" => ["select", [
                "left" => $this->plugin->txt("component_left"),
                "center" => $this->plugin->txt("component_center"),
                "right" => $this->plugin->txt("component_right")
            ]],
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
}