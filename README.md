# maplocation

Description
======
Module for saving google responses for defined latitude and longitude. 

With correct configuring and without overriding you can find *Country*, *City* in model $location attribute. Also for another purposes module defines model $json attribute.

I hope it will be useful for you. 


Installing
======
The preferred way to install this extension is through composer.

```
{
	"require": {
	    "achertovsky/maplocation": "@dev"
    }
}
```

or

```
	composer require achertovsky/maplocation "@dev"
```

update your db schema

```
php yii migrate/up --migrationPath=@vendor/achertovsky/maplocation/migrations
```
Usage
======
to start using it - please, add it to your modules section

you can use your attribute names.

fox example: 
```
'maplocation' => [
	'class' => 'achertovsky\maplocation\Module',
	'attribute' => 'location',
	'latitudeAttribute' => 'latitude',
	'longitudeAttribute' => 'longitude',
	'jsonAttribute' => 'json',
],
```

required model, which have location must have fields, that you assigned as attributes above

fox example:
```
public $location;
public $json;

//in my case latitude and longitude - is table columns
/** @inheritdoc */
public function attributeLabels()
{
    return [
        'latitude' => 'Latitude',
        'longitude' => 'Longitude',
    ];
}
```

and finally for getting and setting location to and from module, you must trigger module events in required model

for example:
```
/*
 * gets location for this instance
 */
public function afterFind()
{
    if (!empty(Yii::$app->getModule('maplocation'))) {
        Yii::$app->getModule('maplocation')->trigGetLocation($this);
    }
    
    return true;
}

/*
 * sets location into module
 */
public function beforeSave($insert)
{
    parent::beforeSave($insert);
    
    if (!empty(Yii::$app->getModule('maplocation'))) {
        Yii::$app->getModule('maplocation')->trigAddLocation($this);
    }
    
    return true;
}
```