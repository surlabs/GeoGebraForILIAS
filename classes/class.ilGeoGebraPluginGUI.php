<?php
declare(strict_types=1);
/**
 * Disclaimer: This file is part of the GeoGebra Repository Object plugin for ILIAS.
 */

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

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

    public function __construct()
    {
        parent::__construct();

        global $DIC;

        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tabs = $DIC->tabs();
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
        $this->edit();
    }

    /**
     * @throws ilCtrlException
     */
    public function edit(): void
    {
        if (!empty($this->getProperties())) {
            $this->setSubTabs("subtab_generic_settings");
        }

        $this->tpl->setContent($this->renderForm($this->ctrl->getLinkTarget($this, 'edit'), $this->buildForm()));
    }

    public function create(): void
    {

    }

    public function getElementHTML(string $a_mode, array $a_properties, string $plugin_version): string
    {
        return '';
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

    private function buildForm(): array
    {
        $inputs = array();

        $inputs[] = $this->factory->input()->field()->text($this->plugin->txt("component_title"))
            ->withRequired(true);

        $inputs[] = $this->factory->input()->field()->file(new UploadServiceGUI(), $this->plugin->txt("component_geogebra_file"))
            ->withRequired(true);

        $inputs[] = $this->factory->input()->field()->numeric($this->plugin->txt("component_width"))
            ->withRequired(true)->withValue(800);

        $inputs[] = $this->factory->input()->field()->numeric($this->plugin->txt("component_height"))
            ->withRequired(true)->withValue(600);

        $inputs[] = $this->factory->input()->field()->checkbox($this->plugin->txt("component_enableShiftDragZoom"))
            ->withValue(true);

        $inputs[] = $this->factory->input()->field()->checkbox($this->plugin->txt("component_showResetIcon"))
            ->withValue(false);

        $inputs[] = $this->factory->input()->field()->select($this->plugin->txt("component_aligment"), [
            "left" => $this->plugin->txt("component_left"),
            "center" => $this->plugin->txt("component_center"),
            "right" => $this->plugin->txt("component_right")
        ])->withValue("left");


        return $inputs;
    }

    private function save(): void
    {

    }
}