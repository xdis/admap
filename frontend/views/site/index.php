<?php
$this->title = '地图demo';
?>

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no, width=device-width">
    <title>输入提示后查询</title>
    <link rel="stylesheet" href="http://cache.amap.com/lbs/static/main1119.css"/>
    <script type="text/javascript" src="http://webapi.amap.com/maps?v=1.4.4&key=90a189af631121b7e731befe99bca569&plugin=AMap.Autocomplete,AMap.PlaceSearch"></script>
    <script type="text/javascript" src="http://cache.amap.com/lbs/static/addToolbar.js"></script>
</head>
<body>
<div id="container"></div>
<div id="myPageTop">
    <table>
        <tr>
            <td  style="height: 82px;">
                <label>请输入关键字：</label>
            </td>
        </tr>
        <tr>
            <td>
                <input id="tipinput"/>
            </td>
        </tr>
    </table>
</div>
<script type="text/javascript">
    //地图加载
    var map = new AMap.Map("container", {
        resizeEnable: true
    });
    //输入提示
    var autoOptions = {
        input: "tipinput"
    };
    var auto = new AMap.Autocomplete(autoOptions);
    var placeSearch = new AMap.PlaceSearch({
        map: map
    });  //构造地点查询类
    AMap.event.addListener(auto, "select", select);//注册监听，当选中某条记录时会触发
    function select(e) {
        placeSearch.setCity(e.poi.adcode);
        placeSearch.search(e.poi.name);  //关键字查询查询
    }

    var imageLayer = new AMap.ImageLayer({
        url : 'http://localhost/own/advanced/frontend/web/tb_ml.jpg',
        bounds: new AMap.Bounds(
            [114.076301, 22.53842],   //左下角
            [114.094454, 22.5515]    //右上角
        ),
        zooms: [15, 18]
    });
    var map = new AMap.Map('container', {
        resizeEnable: true,
        zoom: 15,
        layers: [
            new AMap.TileLayer(),
            imageLayer
        ]
    });
</script>
</body>
</html>