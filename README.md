
### Installation steps

**In case that the previous plugin srGeoGebra is installed in your platform, remove the code within Customizing/global/plugins/Services/COPage/PageComponent/srGeoGebra without uninstalling it**

1. Create subdirectories, if necessary for Customizing/global/plugins/Services/COPage/PageComponent/ or run the following script fron the ILIAS root
   
```bash
mkdir -p Customizing/global/plugins/Services/COPage/PageComponent
cd Customizing/global/plugins/Services/COPage/PageComponent
```

3. In Customizing/global/plugins/Services/COPage/PageComponent/
4. Then, execute:

```bash
git clone https://github.com/surlabs/GeoGebraForILIAS.git ./GeoGebra
git checkout ilias9
```

Ensure you run composer and npm install at platform root before you install/update the plugin
```bash
composer install --no-dev
npm install
```

Run ILIAS update script at platform root
```bash
php setup/setup.php update
```
**Ensure you don't ignore plugins at the ilias .gitignore files and don't use --no-plugins option at ILIAS setup**
