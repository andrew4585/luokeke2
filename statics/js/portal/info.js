//用户点击分享
function Share(id,table) {
    CheckLogin(id,table);
}
//打开分享弹出框
function ShareWindow(id,table) {
    var url = "/Api/Share/shareWindow/id/"+id+"/table/"+table;
    var tblName = [
                 'Group'	:'套系',
                 'Pcustom'	:'客照',
                 'Photo'	:'作品',
                 'Dress'	:'婚纱礼服',
                 'Article'	:'婚嫁常识'];
    $.layer({
        type: 2,
        title: tblName[table]+'分享',
        iframe: { src: url },
        area: ['550px', '450px'],
        offset: ['100px', '']
    });
}
//检查用户是否登陆
function CheckLogin(id,table) {
    $.ajax({
        url: '/Api/Share/CheckLogin',
        type: 'GET',
        dataType: 'json',
        error: function () { layer.msg('呃！系统异常了。', 1, 8); },
        success: function (result) {
            if (!result.status) {
                layer.msg("请登录后分享^_^");
            }else {
                ShareWindow(id,table);
            }
        }

    });
}