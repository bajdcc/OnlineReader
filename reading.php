
<!DOCTYPE html>
<html>
	<head>
		<title>Reading - Powered by bajdcc</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<link href="materialize.css" type="text/css" rel="stylesheet" media="screen,projection" />
		<script type="text/javascript" src="jquery-1.11.1.min.js"></script>
		<script type="text/javascript" src="materialize.js"></script>
		<script type="text/javascript" src="vue.min.js"></script>
		<script type="text/javascript" src="jquery.hotkeys.js"></script>
		<script type="text/javascript" src="jquery-mobile-events.js"></script>
		<style>
		body {
			background: rgb(238,238,238);
			 -webkit-user-select:none; -moz-user-select:none; -ms-user-select:none; user-select:none;
		}
		#app {
			margin: 0 auto;
		}
		a {
			cursor: pointer;
		}
		h1 {
			text-align: center;
			font-size: 26px;
			color: red;
			font-weight: 400;
			margin-top: 60px;
		}
		div.zj p:nth-child(1) {
			display: none;
		}
		div.zj p {
			color: rgb(0, 0, 0);
    		font-size: 16px;
			line-height: 180%;
			width: 750px;
			margin: 25px auto 0;
			text-align: left;
			padding: 10px;
		}
		</style>
	</head>

	<body>
		<script>
			function gup(name) {
				name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
				var regexS = "[\\?&]" + name + "=([^&#]*)";
				var regex = new RegExp(regexS);
				var results = regex.exec(location.search);
				if (results === null) {
					return null;
				}
				else {
					return results[1];
				}
			}
			(function($){$(function(){
				id=gup('id');
				if (!id) return;
				$.getJSON('getmenu.php',{'id':id},function(data){
					window.bookinfo=data['data'];
					if(bookinfo.length===0){
						setTimeout(function(){location.reload()},3000);
						return;
					}
					window.bookname=data['title'];
					window.lastbid=data['lastbid'];
					if (lastbid&&!gup('force'))bid=parseInt(lastbid);
					else {
						bid=gup('bid');
						if (!bid) bid=0;
						bid=parseInt(bid);
					}
					if (bid<0||bid>=bookinfo.length)return;
					setTimeout(initVue,100);
				});
			})})(jQuery);
			function initVue(){
				$(".button-collapse").sideNav();
				var filterTitle = function (value) {
					return value.split('').map(function(obj){
						if(obj==='章')return obj+'   ';return obj;
					}).join('');
				};
				Vue.filter('filterTitle', filterTitle);
				function getdata() {
					$.get('get.php',{'id':id,'bid':bid,'start':bookinfo[bid]['begin'],'end':(bid+1>=bookinfo.length)?0:bookinfo[bid+1]['begin']},function(k){
						v.$set('items',k.split('\n').map(function(k){
							return k.replace(/\s/, '')
						}).filter(function(a){
							return a!==''
						}).map(function(k){
							return '　　'+k;
						}));
						if(v)v.$set('kid',bid);
						scrollTo(0,0);
					});
				}
				getdata();
				old_bid=bid;
				v=new Vue({
				el: '#app',
					data: {
						swipe: false,
						items: ['Loading...'],
						menu: 'menu.php?id='+id,
						loaded: false,
						logo: bookname,
						kid: bid
					},
					computed: {
						title: function(){
							var t = bookinfo[this.kid].name;
							document.title=filterTitle(t)+' - '+bookname;
							return t;
						},
						prevb: function(){return this.kid>=1},
						nextb: function(){return this.kid<bookinfo.length-1},
						prev: function(){return (false) ? (location.pathname+'?id='+id+"&bid="+(this.kid-1)) : '#'},
						next: function(){return (false) ? (location.pathname+'?id='+id+"&bid="+(this.kid+1)) : '#'},
					},
					methods: {
						gotoprev: function(){
							if(bid>=1){
								bid--;
								var state={'id':id,'bid':bid};
								history.pushState(state, document.title, ('?id='+id+"&bid="+(bid)));
								this.gotobyid();
							}else return;
						},
						gotonext: function(){
							if(bid<bookinfo.length-1){
								bid++;
								var state={'id':id,'bid':bid};
								history.pushState(state, document.title, ('?id='+id+"&bid="+(bid)));
								this.gotobyid();
							}else return;
						},
						gotobyid: function(){
							getdata();
						},
						toggleswipe: function(){
							this.swipe=!this.swipe;
						}
					}
				});
				window.onpopstate = function(e){
					e.preventDefault();
					if (history.state){
						var state = e.state;
						id=state.id;
						bid=state.bid;
						v.$options.methods.gotobyid();
					}else{
						bid=old_bid;
						v.$options.methods.gotobyid();
					}
				};
				$(document).bind("swipeleft",function(evt){
					evt.preventDefault();
					if(v.$get('swipe'))
					v.$options.methods.gotonext();
				});
				$(document).bind("swiperight",function(evt){
					evt.preventDefault();
					if(v.$get('swipe'))
					v.$options.methods.gotoprev();
				});
				$(document).bind("keydown",'left',function(evt){
					v.$options.methods.gotoprev();
				});
				$(document).bind("keydown",'right',function(evt){
					v.$options.methods.gotonext();
				});
			}
		</script>
		
		<div id="app">
		<div class="navbar-fixed">
			<nav>
			<div class="nav-wrapper">
			<a href="#!" class="brand-logo hide-on-med-and-down center" v-text="logo"></a>
			<a href="#" data-activates="mobile-demo" class="button-collapse"><i class="mdi-navigation-menu"></i></a>
			<ul class="right hide-on-med-and-down">
				<li><a @click="gotoprev" :class="{ 'hide': !prevb }">上一章</a></li>
				<li><a :href="menu">返回目录</a></li>
				<li><a @click="gotonext" :class="{ 'hide': !nextb }">下一章</a></li>
			</ul>
			<ul class="side-nav" id="mobile-demo">
				<li><a @click="gotoprev" :class="{ 'hide': !prevb }">上一章</a></li>
				<li><a :href="menu">返回目录</a></li>
				<li><a @click="gotonext" :class="{ 'hide': !nextb }">下一章</a></li>
			</ul>
			</div>
			</nav>
		</div>

		<div class="container">
			<div class="margin: 0 auto;">
				<div class="row center">
				<div class="col s4"><a @click="gotoprev" :class="{ 'disabled': !prevb }" class="waves-effect waves-light btn">上一章</a></div>
				<div class="col s4"><a :href="menu" class="waves-effect waves-light btn">目&emsp;录</a></div>
				<div class="col s4"><a @click="gotonext" :class="{ 'disabled': !nextb }" class="waves-effect waves-light btn">下一章</a></div>
				</div>
			</div>
			<div>
				<div class="fixed-action-btn" style="bottom: 200px; right: 24px;"><a @click="toggleswipe" :class="{ 'darken-2': swipe, 'lighten-2': !swipe }" class="waves-effect waves-light blue btn">滑&emsp;动</a></div>
				<div class="fixed-action-btn" style="bottom: 135px; right: 24px;"><a @click="gotoprev" :class="{ 'disabled': !prevb }" class="waves-effect waves-light blue btn">上一章</a></div>
				<div class="fixed-action-btn" style="bottom: 90px; right: 24px;"><a :href="menu" class="waves-effect waves-light blue btn">目&emsp;录</a></div>
				<div class="fixed-action-btn" style="bottom: 45px; right: 24px;"><a @click="gotonext" :class="{ 'disabled': !nextb }" class="waves-effect waves-light blue btn">下一章</a></div>
			</div>
			<div class="row">
				<div class="col s12">
					<h1 v-text="title | filterTitle"></h1>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col s12 zj">
					<p v-for="item of items" v-text="item" track-by="$index"></p>
				</div>
			</div>
			<div class="row">
				<div class="col s12">
					<h1 v-text="title | filterTitle"></h1>
				</div>
			</div>
			<div class="margin: 0 auto;">
				<div class="row center">
				<div class="col s4"><a @click="gotoprev" :class="{ 'disabled': !prevb }" class="waves-effect waves-light btn">上一章</a></div>
				<div class="col s4"><a :href="menu" class="waves-effect waves-light btn">目&emsp;录</a></div>
				<div class="col s4"><a @click="gotonext" :class="{ 'disabled': !nextb }" class="waves-effect waves-light btn">下一章</a></div>
				</div>
			</div>
		</div>
		</div>
	</body>
</html>
