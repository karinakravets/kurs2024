<?php
namespace app\controllers;
use app\models\Dish;
use app\models\Cart;
use app\models\CartItem;
use app\models\User;
use app\models\Order;
use yii\web\UploadedFile;
use Yii;
class DishController extends RestController
{
    public $modelClass = 'app\models\Dish';
    
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update']);
        return $actions; 
    }
    public function actionAll()
    {
        if (Dish::find()) {
            return $this->Response(200, ['data' => Dish::find()->select(['name', 'product_price','photos'])->all()]);
        }
        return $this->Response(204);
    }
    public function actionOne($id)
    {
        $item = Dish::findOne($id);
        if ($item) {
            return $this->Response(200, ['data' => $item]);
        }
        return $this->Response(204);
    }

    public function actionCreate() {
        $user = User::getByToken();
        if (!($user && $user->isAuthorized() && $user->isAdmin())) {
            return $this->Response(403, ['error' => ['message' => 'Доступ запрещен']]);
        }
        $data = Yii::$app->request->post();
        $dish = new Dish();
        $dish->load($data, '');
        if (isset($data['photo'])) {
            $dish->photo = UploadedFile::getInstanceByName('photos');
            if ($this->ValidationError($dish)) return $this->ValidationError($dish);
            $path = Yii::$app->basePath. '/assets/uploads/' . hash('sha256', $dish->photos->baseName) . '.' . $dish->photos->extension;
            $dish->photos->saveAs($path);
            $dish->phots = $path;
        }
        else {
            if ($this->ValidationError($dish)) return $this->ValidationError($dish);
        }
        $dish->save();
        return $this->Response(201, [
            'id' => $dish->id,
            'message' => 'Товар добавлен'
        ]);
    }
        public function actionUpgrade($id) {
            $user = User::getByToken();
            if (!($user && $user->isAuthorized() && $user->isAdmin())) {
                return $this->Response(403, ['error' => ['message' => 'Доступ запрещен']]);
            }
            $data = Yii::$app->request->post();
            $dish = Dish::findOne($id);
            $dish->scenario = Dish::SCENARIO_UPDATE_PRODUCT;
            $dish->load($data, '');
            if (isset($data['photo'])) {
                $dish->photo = UploadedFile::getInstanceByName('photos');
                if ($this->ValidationError($dish)) return $this->ValidationError($dish);
                $path = Yii::$app->basePath. '/assets/uploads/' . hash('sha256', $dish->photos->baseName) . '.' . $dish->photos->extension;
                $dish->photos->saveAs($path);
                $dish->phots = $path;
            }
            else {
                if ($this->ValidationError($dish)) return $this->ValidationError($dish);
            }
            $dish->save();
            return $this->Response(204, [
                'id' => $dish->id,
                'message' => 'Товар обновлен'
            ]);
        
    }
    public function actionDelete($id) {
        $user = User::getByToken();
        if (!($user && $user->isAuthorized() && $user->isAdmin())) {
            return $this->Response(403, ['error' => ['message' => 'Доступ запрещен']]);
        }
        $data = Yii::$app->request->post();
        $dish = Dish::findOne($id);
        if (!$dish) {
            return $this->Response(404, [
                'message' => 'Товар не найден'
            ]);                        
        }
        $dish->delete();
        return $this->Response(204, [
            'id' => $dish->id,
            'message' => 'Товар удален'
        ]);
    }
    //order
    public function actionCreateOrders() 
{ 
    $user = User::getByToken(); 
    if (!($user && $user->isAuthorized())) { 
        return $this->Response(401, ['error' => ['message' => 'Вы не авторизованы']]); 
    } 
    $cart = Cart::find()->where(['user_id' => $user->id])->one(); 
    if (!$cart) { 
        return $this->Response(400, ['error' => ['message' => 'Нет товаров в корзине']]); 
    } 
    $data = Yii::$app->request->getBodyParams(); 

if (empty($data['name']) || empty($data['phone']) || empty($data['address']) || empty($data['payment_method'])) {
    return $this->Response(400, ['error' => ['message' => 'Неправильные данные заказа']]);
}
    
    $data = Yii::$app->request->getBodyParams();
    $order = new Order();
    $order->load($data, ''); 
    $order->user_id = $user->id;
   $order->cart_id = $cart->id;
   $order->name = isset($data['name']) ? $data['name'] : null;
   $order->phone = isset($data['phone']) ? $data['phone'] : null;
   $order->address = isset($data['address']) ? $data['address'] : null;
   $order->payment_method = isset($data['payment_method']) ? $data['payment_method'] : null;
   $order->comment = isset($data['comment']) ? $data['comment'] : null;

   if ($order->save()) {
       return $this->Response(201, ['message' => 'Заказ создан успешно']);
   } else {
       return $this->Response(500, ['error' => ['message' => 'Ошибка при сохранении заказа']]);
   }
    }

    public function actionGetOrders() 
{ 
    $user = User::getByToken();  
    if (!($user && $user->isAuthorized())) {  
        return $this->Response(403, ['message' => 'Доступ запрещен']);  
    }  
     
    if ($user->isAdmin()) { 
        $orders = Order::find()->all(); 
    } else { 
        $orders = Order::find()->where(['user_id' => $user->id])->all(); 

        if (empty($orders)) {  
            return $this->Response(200, ['message' => 'У вас нет заказов']);  
        }  
    }

    return $this->Response(200, $orders);   
}
    
   
    private function calculateTotalPrice($order)
    {
    $totalPrice = 0;
    $cart_item = CartItem::find()->where(['cart_id' => $order->cart_id])->all();

    foreach($cart_item as $item){
        $dish = Dish::findOne(["id"=>$item->product_id]);
        $totalPrice += $dish->product_price;
    }
    return $totalPrice;
    } 


    public function actionUpdateOrderStatus($id) 
    { 
        $order = Order::findOne($id); 
         
    if (!$order) {  
        return $this->Response(404, ['errors' => 'Заказ не найден']);  
    }  

    $data = Yii::$app->request->getBodyParams();  

    if (!isset($data['status'])) { 
        return $this->Response(422, ['errors' => 'Статус заказа не указан']); 
    } 

    if (!in_array($data['status'], ['В стадии рассмотрения', 'Оформлен', 'Готовится', 'Едет к вам','Доставлен'])) {
        return $this->Response(400, [
            'errors' => 'Некорректный статус',
            'validation_error' => [
                'field' => 'status',
                'message' => 'Статус должен быть одним из:В стадии рассмотрения, Оформлен, Готовится, Едет к вам, Доставлен'
            ]
        ]);
    } $order->status=($data['status']);
    $order->save();
    }
   
}
