<?php
namespace app\controllers;
use app\models\Cart;
use app\models\CartItem;
use app\models\Dish;
use app\models\User;
use app\models\Order;
use Yii;
class CartController extends RestController
{
    public $modelClass = 'app\models\User';
    public function actions()
    {$actions = parent::actions();
        unset($actions['delete'], $actions['create']);

        return $actions;
    }
    public function actionAdd() {
        $user = User::getByToken();
        if (!($user && $user->isAuthorized())) {
            return $this->Response(401, ['error' => ['message' => 'Вы не авторизованы']]);
        }
        $product_id = Yii::$app->request->post('product_id');
        if (empty($product_id)) {
            return $this->Response(400, ['error' => ['message' => 'Не указан ID продукта']]);
        }
        $dish = Dish::findOne($product_id);
        if (!$dish) {
            return $this->Response(404, ['error' => ['message' => 'Продукт не найден']]);
        }
        $cart = Cart::find()->where(['user_id' => $user->id])->one();
        if (!$cart) {
            $cart = new Cart();
            $cart->user_id = $user->id;
            if (!$cart->save()) {
                return $this->Response(500, ['error' => ['message' => 'Ошибка при создании корзины']]);
            }
        }
        $cartItem = CartItem::find()->where(['cart_id' => $cart->id, 'product_id' => $product_id])->one();
        if ($cartItem) {
            $cartItem->quantity += 1;  
            $cartItem->save();
        } else {
            $cartItem = new CartItem();
            $cartItem->cart_id = $cart->id;
            $cartItem->product_id = $product_id;
            $cartItem->product_price = $dish->product_price;  
            $cartItem->quantity = 1;
            if (!$cartItem->save()) {
                return $this->Response(500, ['error' => ['message' => 'Ошибка при добавлении товара в корзину']]);
            }
        }
        return $this->Response(200, ['message' => 'Продукт успешно добавлен в корзину'  ]);
    }

    public function actionRemoveCart($id) {
        $user = User::getByToken();
        if (!($user && $user->isAuthorized())) {
            return $this->Response(401, ['error' => ['message' => 'Вы не авторизованы']]);
        }
        $cart = CartItem::find()->where(['user_id' => $user->id])->one();
        if (!$cart) {
            return $this->Response(400, ['error' => ['message' => 'Нет товаров в корзине']]);
        }
        $cart_item = CartItem::find()->where(['cart_id' => $cart->id, 'product_id' => $id])->one();
        if (!$cart_item) {
            return $this->Response(400, ['error' => ['message' => 'Товар не найден в корзине']]);
        }
        $cart_item->delete();
        return $this->Response(204);
    }
    public function actionGetCart() {
        $user = User::getByToken();
        if (!($user && $user->isAuthorized())) {
            return $this->Response(401, ['error' => ['message' => 'Вы не авторизованы']]);
        }
        $cart = Cart::find()->where(['user_id' => $user->id])->one();
        if (!$cart) {
            return $this->Response(400, ['error' => ['message' => 'Нет товаров в корзине']]);
        }
        $cart_items = CartItem::find()->where(['cart_id' => $cart->id]);
        if ($cart_items->count() == 0) {
            return $this->Response(400, ['error' => ['message' => 'Нет товаров в корзине']]);
        }
        $items = [];
        foreach ($cart_items->asArray()->all() as $item) {
            if (isset($items[$item['product_id']])) {
                $items[$item['product_id']]['quantity']++;
            }
            else {
                $items[$item['product_id']] = $item;
                $items[$item['product_id']]['quantity'] = 1;
            }
            $items[$item['product_id']]['total_price'] = $item['product_price'] * $items[$item['product_id']]['quantity'];
        }
        return $this->Response(200,$items);
    }
    
        
 }
