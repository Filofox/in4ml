// Create singleton
var in4ml = {

	Init:function(){
		this.js_lib_interface = new JSLibInterface_jQuery();
	}
};

//var in4ml = {
//	
//	js_lib_interface:null,
//	forms:{},
//
//	Init:function(){
//		this.js_lib_interface = new JSLibInterface_jQuery();
//	},
//
//	RegisterForm:function( defintion ){
//		
//	}
//}

JSLibInterface_jQuery = function(){
}
JSLibInterface_jQuery.prototype.Find = function(){
	alert(1);
}

$( document ).ready
(
	function(){
		in4ml.Init();
	}
);