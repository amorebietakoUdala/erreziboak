<?php


namespace App\Utils;

use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;


class IgnoreTablesListener {
    private $ignoredEntities = null;
    private $ignoredTables = null;


    public function __construct($ignoredEntities) {
        $this->ignoredEntities=$ignoredEntities;
        $this->ignoredTables=array();
    }

    /**
     * Remove ignored entities from Schema
     * This listener is called when the schema as been generated, from entities data mapping (i.e. in doctrine-schema-update or in doctrine:migrations:diff)
     * 
     * @param GenerateSchemaEventArgs $args
     */
    public function postGenerateSchema(GenerateSchemaEventArgs $args)
    {

        $schema = $args->getSchema();
        $em = $args->getEntityManager();

        $ignoredTables = $this->ignoredTables;
//		dump($this->ignoredEntities);die;
        foreach ($this->ignoredEntities as $entityName) {
            $ignoredTables[] = strtolower($em->getClassMetadata($entityName)->getTableName());
        }

        foreach ($schema->getTableNames() as $longTableName) {
            $table=$schema->getTable($longTableName);
            $table_name=strtolower($table->getShortestName($table->getNamespaceName()));

            $fks=$table->getForeignKeys();
            foreach ($fks as $fk) { 
                $foreign_table_name=strtolower($fk->getForeignTableName());

                if (in_array($foreign_table_name, $ignoredTables)) {    //if the fk points to one of the entities i'm ignoring
                    $table->removeForeignKey($fk->getName());   //i remove fk constrains from generated schema (NOT FROM MY CURRENT DB!!!)
//                  dump('removed FK '.$fk->getName().' from '.$table_name.' pointing to '.$foreign_table_name.'.['.implode(', ', $fk->getForeignColumns()).']');
                } 
            }
            if (in_array($table_name, $ignoredTables)) { //if i have to ignore the $table_name table
                $schema->dropTable($longTableName);     //remove the table from generated schema -- NOT FROM DB!!
//              dump('removed ignored table/entity '.$longTableName);
            }

        }

    }
}