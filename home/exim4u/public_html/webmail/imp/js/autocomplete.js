var Autocompleter={};Autocompleter.Base=Class.create({baseInitialize:function(B,C,A){this.element=$(B);this.update=$(C).hide();this.active=this.changed=this.hasFocus=false;this.entryCount=this.index=0;this.observer=null;this.oldElementValue=this.element.value;this.options=Object.extend({paramName:this.element.name,tokens:[],frequency:0.4,minChars:1,onHide:this._onHide.bind(this),onShow:this._onShow.bind(this)},(this._setOptions)?this._setOptions(A):(A||{}));if(!this.options.tokens.include("\n")){this.options.tokens.push("\n")}this.element.writeAttribute("autocomplete","off").observe("blur",this._onBlur.bindAsEventListener(this)).observe(Prototype.Browser.Gecko?"keypress":"keydown",this._onKeyPress.bindAsEventListener(this))},_onShow:function(A,D){var C,B=D.getStyle("position");if(!B||B=="absolute"){C=(Prototype.Browser.IE)?A.cumulativeScrollOffset():[0];D.setStyle({position:"absolute"}).clonePosition(A,{setHeight:false,offsetTop:A.offsetHeight,offsetLeft:C[0]})}new Effect.Appear(D,{duration:0.15})},_onHide:function(A,B){new Effect.Fade(B,{duration:0.15})},show:function(){if(!this.update.visible()){this.options.onShow(this.element,this.update)}if(Prototype.Browser.IE&&!this.iefix&&this.update.getStyle("position")=="absolute"){this.iefix=new Element("IFRAME",{src:"javascript:false;",frameborder:0,scrolling:"no"}).setStyle({position:"absolute",filter:"progid:DXImageTransform.Microsoft.Alpha(opactiy=0)",zIndex:1}).hide();this.update.setStyle({zIndex:2}).insert({after:this.iefix})}if(this.iefix){this._fixIEOverlapping.bind(this).delay(0.05)}},_fixIEOverlapping:function(){this.iefix.clonePosition(this.update).show()},hide:function(){this.stopIndicator();if(this.update.visible()){this.options.onHide(this.element,this.update);if(this.iefix){this.iefix.hide()}}},startIndicator:function(){if(this.options.indicator){$(this.options.indicator).show()}},stopIndicator:function(){if(this.options.indicator){$(this.options.indicator).hide()}},_onKeyPress:function(A){if(this.active){switch(A.keyCode){case Event.KEY_TAB:case Event.KEY_RETURN:this.selectEntry();A.stop();return;case Event.KEY_ESC:this.hide();this.active=false;A.stop();return;case Event.KEY_LEFT:case Event.KEY_RIGHT:return;case Event.KEY_UP:case Event.KEY_DOWN:if(A.keyCode==Event.KEY_UP){this.markPrevious()}else{this.markNext()}this.render();A.stop();return}}else{switch(A.keyCode){case 0:if(!Prototype.Browser.WebKit){break}case Event.KEY_TAB:case Event.KEY_RETURN:return}}this.changed=this.hasFocus=true;if(this.observer){clearTimeout(this.observer)}this.observer=this.onObserverEvent.bind(this).delay(this.options.frequency)},_onHover:function(C){var B=C.findElement("LI"),A=B.readAttribute("acIndex");if(this.index!=A){this.index=A;this.render()}C.stop()},_onClick:function(A){this.index=A.findElement("LI").readAttribute("acIndex");this.selectEntry()},_onBlur:function(A){this.hide.bind(this).delay(0.25);this.active=this.hasFocus=false},render:function(){var A=0;if(this.entryCount){this.update.down().childElements().each(function(B){[B].invoke(this.index==A++?"addClassName":"removeClassName","selected")},this);if(this.hasFocus){this.show();this.active=true}}else{this.active=false;this.hide()}},markPrevious:function(){if(this.index){--this.index}else{this.index=this.entryCount-1}this.getEntry(this.index).scrollIntoView(true)},markNext:function(){if(this.index<this.entryCount-1){++this.index}else{this.index=0}this.getEntry(this.index).scrollIntoView(false)},getEntry:function(A){return this.update.down().childElements()[A]},selectEntry:function(){this.active=false;this.updateElement(this.getEntry(this.index));this.hide()},updateElement:function(D){var E,G,B,C,A,F="";if(this.options.updateElement){this.options.updateElement(D);return}if(this.options.select){B=$(D).select("."+this.options.select)||[];if(B.size()){F=B[0].collectTextNodes(this.options.select)}}else{F=D.collectTextNodesIgnoreClass("informal")}E=this.getTokenBounds();if(E[0]!=-1){A=this.element.value;G=A.substr(0,E[0]);C=A.substr(E[0]).match(/^\s+/);if(C){G+=C[0]}this.element.value=G+F+A.substr(E[1])}else{this.element.value=F}this.element.focus();if(this.options.afterUpdateElement){this.options.afterUpdateElement(this.element,D)}this.oldElementValue=this.element.value},updateChoices:function(C){var B,A=0;if(!this.changed&&this.hasFocus){this.update.update(C);B=this.update.down().childElements();this.entryCount=B.size();B.each(function(D){D.writeAttribute("acIndex",A++);this.addObservers(D)},this);this.stopIndicator();this.index=0;if(this.entryCount==1&&this.options.autoSelect){this.selectEntry()}else{this.render()}}},addObservers:function(A){$(A).observe("mouseover",this._onHover.bindAsEventListener(this)).observe("click",this._onClick.bindAsEventListener(this))},onObserverEvent:function(){this.changed=false;if(this.getToken().length>=this.options.minChars){this.getUpdatedChoices()}else{this.active=false;this.hide()}this.oldElementValue=this.element.value},getToken:function(){var A=this.getTokenBounds();return this.element.value.substring(A[0],A[1]).strip()},getTokenBounds:function(){var H,E,C,D,G,F=0,I=this.element.value,J=I.length,B=-1,A=Math.min(I.length,this.oldElementValue.length);if(I.strip().empty()){return[-1,0]}H=A;for(E=0;E<A;++E){if(I[E]!=this.oldElementValue[E]){H=E;break}}D=(H==this.oldElementValue.length?1:0);for(C=this.options.tokens.length;F<C;++F){G=I.lastIndexOf(this.options.tokens[F],H+D-1);if(G>B){B=G}G=I.indexOf(this.options.tokens[F],H+D);if(G!=-1&&G<J){J=G}}return[B+1,J]}});Ajax.Autocompleter=Class.create(Autocompleter.Base,{initialize:function(C,D,B,A){this.baseInitialize(C,D,A);this.options=Object.extend(this.options,{asynchronous:true,onComplete:this._onComplete.bind(this),defaultParams:$H(this.options.parameters)});this.url=B;this.cache=$H()},getUpdatedChoices:function(){var B,A=this.getToken(),C=this.cache.get(A);if(C){this.updateChoices(C)}else{B=Object.clone(this.options.defaultParams);this.startIndicator();B.set(this.options.paramName,A);this.options.parameters=B.toQueryString();new Ajax.Request(this.url,this.options)}},_onComplete:function(A){this.updateChoices(this.cache.set(this.getToken(),A.responseText))}});