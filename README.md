<div alt style="text-align: center; transform: scale(.5);">
	<picture>
		<source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/surlabs/GeoGebraForILIAS/ilias8/templates/images/GitBannerGeoGebra.png" />
		<img alt="STACK" src="https://raw.githubusercontent.com/surlabs/GeoGebraForILIAS/ilias8/templates/images/GitBannerGeoGebra.png" />
	</picture>
</div>

# GeoGebra Page Component Plugin for ILIAS 8

Welcome to the official repository for GeoGebra Page Component Plugin for ILIAS
This Open Source ILIAS Plugin based on the previous srGeoGebra plugin, has been reworked and is currently maintained by [SURLABS](https://www.surlabs.com)

## What is GeoGebra for ILIAS?

GeogebraForILIAS is a plugin that integrates interactive Geogebra materials into ILIAS, enabling users to explore mathematical and scientific concepts through dynamic visualizations and tools, enhancing both teaching and learning experiences.

## Installation & Update

### Software Requirements
This GeoGebra version 8.0 requires [PHP](https://php.net) version 7.4 or 8.0.x to work properly on your ILIAS 8 platform

### Installation steps

If the srGeoGebra plugin is installed on your platform, **remove the code found under Customizing/.../srGeoGebra but do not uninstall the plugin itself.**

1. Create subdirectories, if necessary for Customizing/global/plugins/Services/COPage/PageComponent/ or run the following script fron the ILIAS root
   
```bash
mkdir -p Customizing/global/plugins/Services/COPage/PageComponent
cd Customizing/global/plugins/Services/COPage/PageComponent
```

3. In Customizing/global/plugins/Services/COPage/PageComponent/ 
4. Then, execute:

```bash
git clone https://github.com/surlabs/GeoGebraForILIAS.git ./GeoGebra
cd GeoGebra
git checkout ilias8
```

Ensure you run composer install at platform root before you install/update the plugin
```bash
composer install --no-dev
```

Run ILIAS update script at platform root
```bash
php setup/setup.php update
```
**Ensure you don't ignore plugins at the ilias .gitignore files and don't use --no-plugins option at ILIAS setup**

# Authors
* Initially created by studer + raimann ag, switzerland
* Further maintained by fluxlabs ag, switzerland
* Reworked and currently maintained by SURLABS, spain [SURLABS](https://surlabs.com)

# Bug Reports & Discussion
- Bug Reports: [Mantis](https://www.ilias.de/mantis) (Choose project "ILIAS plugins" and filter by category "GeoGebra")

# Version History
* The version 9.x.x for **ILIAS 9** developed and maintained by SURLABS can be found in the Github branch **ilias9**
* The version 8.x.x for **ILIAS 8** developed and maintained by SURLABS can be found in the Github branch **ilias8**
* The previous plugin versions for ILIAS <8 is archived. It can be found in https://github.com/fluxapps/srGeoGebra
