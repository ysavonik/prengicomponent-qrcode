function objCheckboxOnclick(id, permission)
{
    $.ajax({
        type: "POST",
        url: "/qrcode/objectsave?id="+id+"&p="+permission,
        contentType: "application/json; charset=utf-8",
        data: { id: id, permission: permission },
        dataType: "json",
    });
}
function warCheckboxOnclick(id, permission)
{
    $.ajax({
        type: "POST",
        url: "/qrcode/warehousesave?id="+id+"&p="+permission,
        contentType: "application/json; charset=utf-8",
        data: { id: id, permission: permission },
        dataType: "json",
    });
}
