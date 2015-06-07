<div class="container-fluid padding_0">
  <div class="block-title row">
    <div class="<?=(@$search_path ?'col-sm-7':'');?>">
      <h1><span class="glyphicon <?=($_component['icon']?$_component['icon']:'glyphicon-ok');?>"></span>
        <?=(@$title ? $title : $_component['title']);?>
      </h1>
      <p class="visible-xs-block">&nbsp;</p>
    </div>
    <? if (@$search_path) { ?>
      <div class="col-sm-5 text-right">
        <form action="<?=$search_path;?>" method="GET" class="form-inline">
          <div class="form-group">
            <input type="text" name="title" value="<?=$search_title;?>" <?=($search_title ? 'autofocus="true"' : '');?> class="form-control input-sm" id="searchTitle" placeholder="Введите название">
          </div>
          <div class="form-group">
          <button type="submit" class="btn btn-default btn-sm">Поиск</button>
          </div>
        </form>
      </div>
    <? } ?>
  </div>
</div>
<div class="container-fluid">
  <div class="clearfix">
    <a class="btn btn-default btn-xs pull-left" href="/admin<?=$_component['path'];?>"><span class="glyphicon glyphicon-backward"></span> Назад</a>
    <a  class="btn btn-default btn-xs pull-right" href="/admin<?=$_component['path'];?>clients_report/">Очистить параметры</a>   
  </div><br/>
  <?=$form;?><br/>
  <table class="table table-report table-hover table-bordered">
    <tr>
      <th>ID</th>
      <th>Название</th>
      <th>Город</th>
      <?foreach ($client_params as $key => $value) {?>
        <th><?=$value['title'];?></th>
      <?}?>
    </tr>
    <? foreach ($items as $item) { ?>
      <tr onclick="window.open('/admin/clients/edit_client/<?=$item['id'];?>/','_client_<?=$item['id'];?>')">
        <td><?=$item['id'];?></td>
        <td><?=$item['title'];?></td>
        <td><?=$item['city'];?></td>
        <?foreach ($client_params as $key => $value) {?>
          <td><?=@$item['params']['param_'.$value['id'].'_'.$_language];?></td>
        <?}?>
      </tr>
    <? } ?>
  </table>
  <?=(isset($pagination) && $pagination ? $pagination : '');?>
  <a class="btn btn-default btn-xs" href="/admin<?=$_component['path'];?>"><span class="glyphicon glyphicon-backward"></span> Назад</a>
</div>
<br/>