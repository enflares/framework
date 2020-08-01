<?php
namespace enflares\Db;

use RuntimeException;
use enflares\System\Model;
use enflares\System\Module;
use enflares\System\InvalidException;

/**
 * Class SchemaFactory
 * @package enflares\Db
 */
class SchemaFactory extends Schema
{
    const ENTITY_NAME = NULL;
    const ENTITY_PREFIX = NULL;
    
    /**
     * Create the entity table
     * 
     * @return SchemaTable
     */
    public static function table()
    {
        return static::createTable(strtr(static::ENTITY_NAME, '/\\', '_'), static::ENTITY_PREFIX);
    }

    /**
     * Creates/Updates the data table and the class files
     * @return array|string
     */
    public static function install()
    {
        return static::commit(static::table());
    }

    /**
     * Data for initialization
     *
     * @return array|void
     */
    public static function seed()
    {
    }

    /**
     * Performs the data migration
     * @param SchemaTable $table
     * @return array|string
     */
    public static function commit(SchemaTable $table)
    {
        try{
            $seeds = static::seed();
            return array_merge(
                (array)$table->commit($seeds),
                (array)static::createClass($table)/*,
                (array)static::createRelation($table)*/
            );
        }catch(\Exception $ex) {
            return $ex->getMessage();
        }
    }   

    /**
     * @return bool|void
     * @throws \enflares\System\Exception
     */
    public static function createAbstractModel()
    {
        $class = 'App\\AbstractModel';
        if( !class_exists($class) || !is_subclass_of($class, Model::class) ) {
            if( $file = realpath(__DIR__ . '/resources/abstract_model.php') ) {
                if( !realpath($dest = map('app', 'AbstractModel.php')) ) {
                    return copy($file, $dest);
                }else{
                    return InvalidException::trigger('Unable to create "AbstractModel" under the App path');
                }
            }
        }
        return TRUE;
    }

    /**
     * @return bool|void
     * @throws \enflares\System\Exception
     */
    public static function createAbstractModule()
    {
        $class = 'App\\AbstractModule';
        if( !class_exists($class) || !is_subclass_of($class, Module::class) ) {
            if( $file = realpath(__DIR__ . '/resources/abstract_module.php') ) {
                if( !realpath($dest = map('app', 'AbstractModule.php')) ) {
                    return copy($file, $dest);
                }else{
                    return InvalidException::trigger('Unable to create "AbstractModule" under the App path');
                }
            }
        }
        return TRUE;
    }

    /**
     * @return bool|void
     * @throws \enflares\System\Exception
     */
    public static function createAbstractRelation()
    {
        $class = 'App\\AbstractRelation';
        if( !class_exists($class) || !is_subclass_of($class, Model::class) ) {
            if( $file = realpath(__DIR__ . '/resources/abstract_relation.php') ) {
                if( !realpath($dest = map('app', 'AbstractRelation.php')) ) {
                    return copy($file, $dest);
                }else{
                    return InvalidException::trigger('Unable to create "AbstractRelation" under the App path');
                }
            }
        }
        return TRUE;
    }

    /**
     * @param SchemaTable $table
     * @return array|string
     * @throws \enflares\System\Exception
     */
    public static function createClass(SchemaTable $table)
    {
        // initialize
        if( !static::createAbstractModel() ) {
            return 'Fail to create abstract model class file';
        }

        if( !static::createAbstractModule() ) {
            return 'Fail to create abstract module class file';
        }

        if( !static::createAbstractRelation() ) {
            return 'Fail to create abstract relation class file';
        }

        $results = [];
        try{
            // individual model
            if( !($result = static::createModelClass($name=$table->name(), $table->prefix(), $table->columns())) ) {
                return 'Fail to create model class file';
            }
            $results[] = 'Model class created: '.$result;

            if( !($result = static::createModuleClass($name)) ) {
                $results[] = 'Fail to create module class file';
                return $results;
            }
            $results[] = 'Module class created: '.$result;

            foreach( $table->relations() as $relation ) {
                try{
                    $result = static::createRelationClass(static::class, $relation);
                    if( $result ) $results[] = 'Relation class created: '.$result;
                    else $results[] = 'Fail to create class for relation with '.$relation;
                }catch(\Exception $ex){
                    $results[] = $ex->getMessage();
                }
            }
        } catch(\Exception $ex) {
            $results[] = $ex->getMessage();
        }

        return $results;
    }

    /**
     * @param $name
     * @return array
     */
    public static function parseName($name)
    {
        if( ($pos = strpos($name, '/'))!==FALSE ) {
            $namespace = substr($name, 0, $pos);
            $class = substr($name, $pos+1);
        }elseif( ($pos = strpos($name, '_'))!==FALSE ) {
            $namespace = substr($name, 0, $pos);
            $class = substr($name, $pos+1);
        }else{
            $namespace = NULL;
            $class = $name;
        }

        return [$namespace, $class];
    }

    /**
     * @param $name
     * @param $prefix
     * @param $columns
     * @return string|string[]
     */
    public static function createModelClass($name, $prefix, $columns) 
    {
        if( !realpath($file = __DIR__ . '/resources/model_class.php') ) {
            throw new RuntimeException('Template file "model_class" is missing');
        }

        list($namespace, $class) = static::parseName($name);
        
        $properties = array_keys($columns);

        $args = [
            'namespace_name' => $namespace ? ('Model\\'.ucfirst($namespace)) : 'Model',
            'class_name'     => $className = str_replace(' ', '', ucwords(strtr($class, '_', ' '))),
            'table_name'     => strtr($name, '/', '_'),
            'table_prefix'   => $prefix,
            'properties'     => implode(';'.PHP_EOL . '    protected $', $properties)
        ];

        if( !is_dir($path = map('app', 'Model', strtr(ucwords(strtr($namespace, '\\', ' ')), ' ', '/')))) mkdir($path, 0777, TRUE);
        $code = str_replace(array_keys($args), array_values($args), file_get_contents($file));
        if( file_put_contents( $path . '/' . $className . '.php', $code) )
            return $className;
        return NULL;
    }

    /**
     * @param $name
     * @return string|void
     */
    public static function createModuleClass($name) 
    {
        if( !realpath($file = __DIR__ . '/resources/module_class.php') ) {
            throw new RuntimeException('Template file "module_class" is missing');
        }

        list($namespace, $class) = static::parseName($name);

        $className = str_replace(' ', '', ucwords(strtr($class, '_', ' ')));

        $args = [
            'namespace_name' => $namespace ? ('Module\\'.ucfirst($namespace)) : 'Module',
            'class_name'     => $moduleName = static::createModuleName($className),
            'model_name'     => ucfirst(trim($namespace . '\\' . $className, '\\'))
        ];

        $path = dirname(static::createCommonModule($namespace));
        $dest = $path . '/' . $moduleName . '.php';
        if( !realpath($dest) ) {
            $code = str_replace(array_keys($args), array_values($args), file_get_contents($file));
            if( !file_put_contents( $dest, $code) ) return;
        }
            
        return $moduleName;
    }

    /**
     * @param $factory
     * @param $relation
     * @param array|NULL $properties
     * @return string|string[]
     * @throws \enflares\System\Exception
     */
    public static function createRelationClass($factory, $relation, Array $properties=NULL) 
    {
        if( !realpath($file = __DIR__ . '/resources/relation_class.php') ) {
            throw new RuntimeException('Template file "relation_class" is missing');
        }

        list($namespace1, $class1) = static::parseName($factory::ENTITY_NAME);
        list($namespace2, $class2) = static::parseName($relation::ENTITY_NAME);

        $secondaryPart = basename($relation, 'Factory');

        if( !$properties ) $properties = [];
        array_unshift($properties, $factory::ENTITY_PREFIX.'_id', 
                                    strtolower($secondaryPart).'_id'/*,
                                    'relation_sort_order',
                                    'relation_created_at',
                                    'relation_deleted_at'*/);

        $class = $class1 . '_'. $secondaryPart;
        $className = str_replace(' ', '', ucwords(strtr($class, '_', ' ')));

        // create relation table
        $comment = 'One '.ucwords(strtr($factory::ENTITY_PREFIX, '_', ' ')) . ' has many ' . static::singularToPlural(ucwords(strtr($secondaryPart, '_', ' ')));
        $table = new SchemaTable(trim($namespace1.'_'.$class, '_'), 'relation', NULL, 'MyISAM', $comment);
        $table->createBigInt($properties[0])->primary(TRUE);
        $table->createBigInt($properties[1])->primary(TRUE);
        for($i=2; $i<count($properties); $i++) {
            $table->createInt($properties[$i])->default(0);
        }
        $table->commit();

        $primary = trim(str_replace(' ', '', ucwords(strtr($namespace1, '_', ' '))) . '\\' . str_replace(' ', '', ucwords(strtr($class1, '_', ' '))), '\\');
        $secondary = trim(str_replace(' ', '', ucwords(strtr($namespace2, '_', ' '))) . '\\' . str_replace(' ', '', ucwords(strtr($class2, '_', ' '))), '\\');
        
        $args = [
            'namespace_name' => $namespace1 ? ('Model\\'.ucfirst($namespace1)) : 'Model',
            'class_name'     => $className,
            'primary_name'   => $primary,
            'secondary_name' => $secondary,
            'properties'     => implode(';'.PHP_EOL . '    protected $', $properties)
        ];

        if( !is_dir($path = map('app', 'Model', strtr(ucwords(strtr($namespace1, '\\', ' ')), ' ', '/')))) mkdir($path, 0777, TRUE);
        $code = str_replace(array_keys($args), array_values($args), file_get_contents($file));
        if( file_put_contents( $path . '/' . $className . '.php', $code) )
            return $className;
        return NULL;
    }

    /**
     * @param $namespace
     * @return string
     */
    public static function createCommonModule($namespace)
    {
        $path = map('app', 'Module', $namespace = strtr(ucwords(strtr($namespace, '\\', ' ')), ' ', '/'));

        if( !realpath($file = __DIR__ . '/resources/module.php') )
            throw new RuntimeException('Template file "module" is missing');

        if( !is_dir($path) ) 
            mkdir($path, 0777, TRUE);

        if( !realpath($dest = $path . '/Module.php') ) {
            $code = str_replace('namespace_name', trim('Module\\'.strtr($namespace, '/', '\\'), '\\'), file_get_contents($file));
            file_put_contents( $dest = $path . '/Module.php', $code);
        }

        return $dest;
    }

    /**
     * Generates a name from a model(singular) to a module(plural)
     * Add more exceptions here if needed
     * @param $modelName
     * @return string
     */
    public static function createModuleName($modelName)
    {
        switch( $modelName ){
            case 'quiz':
                return 'quizzes';
            break;

            default:
                return static::singularToPlural($modelName);
        }        
    }

    /**
     * Converts a word in singular form to plural
     * @param $word
     * @return string
     */
    public static function singularToPlural($word)
    {
        switch( substr($word, -2) ){
            case 'ch': case 'sh':
                return $word . 'es';
            break;

            case 'fe':
                return substr($word, 0, -2).'ves';
            break;
        }

        switch( substr($word, -1) ){
            case 'y':
                if( in_array(substr($word, -2, 1), ['a', 'e', 'i', 'o', 'u']) ) return $word.'s';
                return substr($word, 0, -1) . 'ies';
            break;
            
            case 'f':
                return substr($word, 0, -1) . 'ves';
            break;

            case 'x': case 's': case 'z':
                return $word . 'es';
            break;

            default:
                return $word . 's';
        }
    }
}

