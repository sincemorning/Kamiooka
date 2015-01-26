/*ページの初期化処理*/
function initializePage() {
  $("#pageBody").hide();
  $("#pageImg").hide();
  var size;
  Tabletop.init( {
    key: KeyHeaderMenu,
    callback: function(data, tabletop) {createMenu("menu", data)  },
               simpleSheet: true 
  } )
} /* initializePage */

/*
 *ヘッダーメニューを生成する
 * @param obj      書き込み先DOMのID
 * @param jsonData メニュー表示文字列、リンク先URLを保持するJson
 */
function createMenu(obj, jsonData) {
  var menuSize = 0;
  for(var i in jsonData){
    $("#" + obj).append("<li><a href=\"#\" onclick=\"pageLoadAction('"+ jsonData[i].menuurl + "', '" + jsonData[i].img + "') ;return false;\">" + jsonData[i].menuname + "</a></li>");
    menuSize++;
  }
  /*メニューの要素数に合わせてcssを動的に編集する*/
  
  /*ページサイズに合わせてメニューのDOMのサイズを変更する*/
  changeWithWindow();
  $("#loading").hide();
  /*一番最初に表示するのはトップページ*/
  $("#pageBody").load("./pages/top.html");
  $("#pageImg").css('background-image', 'url("./img/top.jpg")');
  $("#pageBody").show();
  $("#pageImg").show();
} /*createMenu*/

/*ページサイズ（幅）に合わせて動的に変化する部分を操作する*/
function changeWithWindow() {
  var w = window.innerWidth ? window.innerWidth: $(window).width();
  var h = window.innerHeight ? window.innerHeight: $(window).height();
  /*フルで指定するとIEなどで若干飛び出るので、サイズ調整*/
  $("#menu").width(w - 30); 
  $("#pageBody").width(w - 35);
  $("#pageBody").height(h - 50);
  $("#pageImg").width(w - 35);
  $("#pageImg").height(h - 50);
} /*changeWithWindow*/

/*メニューボタン押下時の動き*/
function pageLoadAction(url, img) {
  $("#pageBody").hide();
  $("#pageBody").html("");
  $("#pageBody").load("./pages/" + url);
  if(img.length != undefined) {
    $("#pageImg").css('background-image', 'url("./img/' + img + '")');
  }
  $("#pageImg").show();
  $("#pageBody").fadeIn(1300);
} /*pageLoadAction*/


