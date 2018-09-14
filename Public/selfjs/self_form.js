/*
 * 默认
 * 提交POST表单
 * 检查 form_check类的value值是否为空 为空提示placeholder的内容
 * <input type="text" id="name" name="name" class="form_check layui-input" autocomplete="off" placeholder="请填写菜单名称">
 * @
 */
function ajaxForm(id,url,callback) {
    var default_class       =   '.form_check';
    var obj                 =   new Object();
    var type                =   "POST";
    var current_callback    =   callback;



    $(id).click(function(){
        var flag            =   true;
        var length          =   $(default_class).length;

        var abc             =   $("form").serializeArray();



        $(default_class).each(function(index){

            if($(this).val() == ''){
                layer.msg($(this).attr('placeholder'));
                return false;
            }
            if(length == (Number(index)+1)){
                flag        =   false;
            }
        });

        return false;

        if (flag) {
            return false;
        }

        $(default_class).each(function(){
            var input_name  =   $(this).attr('name');
            obj[input_name] =   $(this).val();
        });
        $.ajax({
            url:url,
            data:obj,
            type:type,
            success:function(res){
                current_callback(res);
            }
        });

    });
}
