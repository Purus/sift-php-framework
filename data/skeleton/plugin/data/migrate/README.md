# Migrations

Place each schema to version directory like:

 * `1.0.0`
    * `1_add_column.php` - migration 1.
    * `2_add_index_to_column.php` - migration 2.
 * `1.1.0`
    * `1_drop_column.php` - migration 1.

Plugin installer will when upgrading the plugin look inside directories for migration scripts and performs the migration based on current version and the version being installed.

See `sfPluginInstaller` class and custom installer in plugin `/lib/installer` directory.

## Migration class

Migration should inherit from `myDoctrineMigration` (which inherits from `Doctrine_Migration_Base`) and should look like:


    /**
     * Class name should use plugin name and version prefix
     */
    class myPluginNameVersion100AddColumn extends myDoctrineMigration {

        // upgrading 
        public function up()
        {
        }
        
        public function down()
        {
        }

    }

