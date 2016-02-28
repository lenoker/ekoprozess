$(document).ready(function(){
 
})

/**
* Отправление прихода в учет остатков
*/
function sendMovement(obj){
  return send_confirm('Вы уверены, что хотите отправить на склад? После выполнения объект будет учтен в остатках, его нельзя будет отредактировать и удалить.',
    '',{},
    function(){
      submit_form(obj,'reload','sendMovement/');
    },
    obj
  );
}

/**
* Подсчет остатков сырья на складе
*/
function updateRestProduct(obj){
  var form, store_type_id, client_id, products, form_block, rest;
  form =  $(obj).parents('form');
  // смотрим client_id и пересчитываем все остатки по указанному вторсырью
  store_type_id = form.find('input[name="store_type_id"]').val();
  client_id = form.find('select[name="client_id"]').val();
  if(store_type_id && client_id){
    // находим все select-ы с вторсырьем и запрашиваем остатки
    products = form.find('select[name="product_id[]"]');
    products.each(function(key,item){
      // если значение указано, запрашиваем остатки
      if($(item).val()){
        $.post('/admin/store/get_rest_product/',
          {store_type_id: store_type_id,client_id: client_id,product_id:$(item).val()},
          function(result){
            form_block = $(item).parents('.form_block');
            // Обнуляем остатки
            form_block.find('.rest, .rest_all').text('0.00');
            if(result){
              form_block.find('.rest').text(result.rest);
              form_block.find('.rest_all').text(result.rest_all);
            }
          },
        'json')
      }
    })
  }
}