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
    private UploadServiceGUI $uploader;

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
        $this->edit();
    }

    /**
     * @throws ilCtrlException
     */
    public function edit(): void
    {
        $this->ctrl->redirect($this, 'create');
    }

    /**
     * @throws ilCtrlException
     */
    public function create(): void
    {
        global $DIC;

        $parent_id = ilObject::_lookupObjectId((int) filter_input(INPUT_GET, "ref_id"));

        $request = $DIC->http()->request();

        $form = $this->factory->input()->container()->form()->standard(
            "#",
            $this->buildForm()
        );

        if ($request->getMethod() == "POST") {
            $form = $form->withRequest($request);

            $result = $form->getData();

            if ($result) {
                dump($result["title"]);
                dump($this->uploader->getExistingFileInfoURL());
                dump($this->uploader->getInfoResult($result["file"][0])->getName());
                exit();
            }
        }

        $this->tpl->setContent($this->renderer->render($form));
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

        $inputs["title"] = $this->factory->input()->field()->text($this->plugin->txt("component_title"))
            ->withRequired(true);

        $inputs["file"] = $this->factory->input()->field()->file($this->uploader, $this->plugin->txt("component_geogebra_file"))
            ->withRequired(true);

        $inputs["widht"] = $this->factory->input()->field()->numeric($this->plugin->txt("component_width"))
            ->withRequired(true)->withValue(800);

        $inputs["height"] = $this->factory->input()->field()->numeric($this->plugin->txt("component_height"))
            ->withRequired(true)->withValue(600);

        $inputs["enableShiftDragZoom"] = $this->factory->input()->field()->checkbox($this->plugin->txt("component_enableShiftDragZoom"))
            ->withValue(true);

        $inputs["showResetIcon"] = $this->factory->input()->field()->checkbox($this->plugin->txt("component_showResetIcon"))
            ->withValue(false);

        $inputs["aligment"] = $this->factory->input()->field()->select($this->plugin->txt("component_aligment"), [
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