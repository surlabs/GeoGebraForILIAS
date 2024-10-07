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
