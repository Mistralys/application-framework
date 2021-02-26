/**
 * Base class for simple class inheritance: allows creating class skeletons
 * and multiple inheritance.
 * 
 * Create a class skeleton with constructor:
 * 
 * <pre>
 * var Skeleton = 
 * {
 *     'name':null,
 *     
 *     init:function(name) 
 *     {
 *         this.name = name;
 *     },
 *     
 *     SomeMethod:function()
 *     {
 *     }
 * };
 * </pre>
 * 
 * Create the base class object:
 * 
 * <pre>
 * Skeleton = Class.extend(Skeleton);
 * </pre>
 * 
 * Instantiate a class:
 * 
 * <pre>
 * var TRex = new Skeleton('James'); 
 * </pre>
 * 
 * 
 * @package Application
 * @author John Resig
 * @link http://ejohn.org/blog/simple-javascript-inheritance
 * @class Class
 */
(function(){
  var initializing = false, fnTest = /xyz/.test(function(){xyz;}) ? /\b_super\b/ : /.*/;
  this.Class = function(){};
  Class.extend = function(prop) {
    var _super = this.prototype;
    initializing = true;
    var prototype = new this();
    prototype.RequireMethod = function(name) {throw new ApplicationException("The method "+name+" must be implemented.");};
    initializing = false;
    for (var name in prop) {
      prototype[name] = typeof prop[name] == "function" &&
        typeof _super[name] == "function" && fnTest.test(prop[name]) ?
        (function(name, fn){
          return function() {
            var tmp = this._super;
            this._super = _super[name];
            var ret = fn.apply(this, arguments);        
            this._super = tmp;
            return ret;
          };
        })(name, prop[name]) :
        prop[name];
    }
    function Class() {
      if ( !initializing && this.init )
        this.init.apply(this, arguments);
    }
    Class.prototype = prototype;
    Class.prototype.constructor = Class;
    Class.extend = arguments.callee;
    return Class;
  };
})();