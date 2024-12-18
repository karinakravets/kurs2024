<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "dish".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $price
 * @property string $weight
 * @property string $kkal
 * @property string $photos
 *
 * @property CartItem[] $cartItems
 */
class Dish extends \yii\db\ActiveRecord
{
    const SCENARIO_UPDATE_PRODUCT = 'update';
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'dish';
    }
    public function scenarios()
    {

        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_UPDATE_PRODUCT] = [
        'name','description','product_price','weight','kkal','photos'
   ];
        return $scenarios;
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'description', 'product_price', 'weight', 'kkal', 'photos'], 'required','except'=>self::SCENARIO_UPDATE_PRODUCT],
            [['description'], 'string'],
            [['photos'], 'safe'],
            [['name', 'weight', 'kkal'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Имя',
            'description' => 'Описание',
            'product_price' => 'Цена',
            'weight' => 'Вес',
            'kkal' => 'Ккал',
            'photos' => 'Фото',
        ];
    }

    /**
     * Gets query for [[CartItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCartItems()
    {
        return $this->hasMany(CartItem::class, ['product_id' => 'id']);
    }
}
