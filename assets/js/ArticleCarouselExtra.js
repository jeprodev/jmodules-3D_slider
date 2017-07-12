(function($){
    $.fn.ArticleCarouselExtra = function (opts) {
        var defaults = {
            activeClass: 'selected',
            container: 'slide',
            selector: 'item',
            scale : 1,
            scroll: 1,
            distance:1,
            circular:true,
            current:3,
            animation: 'Rotate3D',
            max_width : 400,
            minimal_ratio:1,
            radius:{x:0, y : 10 },
            margin:25,
            offset_angle:-195,
            center : { x: 0, y: 0},
            center_offset:{
                x:-100, y: 0
            },
            fx : {duration: 300 },
            interval:10,
            delay:10,
            auto_start: true,
            elements : [],
            rotation_transition : undefined,
            rotation_scale : undefined,
            reflexion_height: 0.2,
            z_index : 32,
            power_exponent : 0.85,
            prefixes: ['-webkit', '-moz', '-o-', '-ms-'],
            timer : undefined,
            running : true,
            reverse : false
        };
        
        var options = $.fn.extend(defaults, opts);
        var carouselObj = this;
        
        return carouselObj.each(function () {
            initializeCarousel();
        });
        
        
        function initializeCarousel() {
            $(document).ready(function () {
                var container = $("#" + options.container);
                options.elements = container.find("." + options.selector);

                if(options.scale !== undefined){
                    var images = container.find("img");
                    var ie8 = $.browser.ie && $.browser.version < 9;
                    images.each(function(index, img) {
                        img = jQuery(img);
                        img.parent().css('left', ie8 ? '0' : '5px');
                        img.parent().css('position', 'absolution');
                        img.parent().parent().css('maxWidth', options.max_width + 'px');
                        img.parent().parent().css('overflow', 'visible');
                        addReflexion(img);
                    });
                }

                /** Initialize 3D rotation **/
                if(options.animation === 'Rotate3D'){
                    initialize3DRotation();
                }

                initializeTimer();
                if(options.auto_start){
                    startSlider();
                }else{
                    stopSlider();
                }
            });
        }

        function addReflexion(img) {
            try{
                var reflexionWrapper = document.createElement('div');
                var imageCopy = img,
                    newClasses = imageCopy.removeClass('reflected').className,
                    reflexionHeight = Math.floor(imageCopy.height * options.reflexion_height),
                    divHeight = Math.floor(imageCopy.height * (1 + options.reflexion_height)),
                    reflexionWidth = imageCopy.width;

                if($.browser.ie && $.browser.version < 9){
                    if(newClasses){ reflexionWrapper.className = newClasses; }
                    imageCopy.className = 'reflected';
                    reflexionWrapper.style.cssText = imageCopy.style.cssText;
                    imageCopy.style.css('vertical-align', 'bottom');

                    var reflexion = document.createElement('img');
                    reflexion.setAttribute('src', imageCopy.src);
                    reflexion.setAttribute('width', reflexionWidth);
                    reflexion.setAttribute('height',  imageCopy.height);
                    reflexion.style.display = 'block';
                    reflexion.style.marginBottom = (imageCopy.height - reflexionHeight) + 'px';
                    reflexion.style.filter = 'flipv prodid:DXImageTransform.Microsoft.Alpha(opacity=' + (options.opacity * 100) + ', style=1, finishOpacity=0, startx=0, starty=0, finishx=0, finishy=' + (options.reflexion_height * 100) + ')';

                    reflexionWrapper.css('height', divHeight + 'px');
                    reflexionWrapper.css('width', reflexionWidth + 'px');
                    imageCopy.parentNode.replaceChild(reflexionWrapper, imageCopy);

                    reflexionWrapper.appendChild(imageCopy);
                    reflexionWrapper.appendChild(reflexion);
                }
            }catch(ignored){
                //alert(ignored);
                //alert("error while setting reflexion");
            }
        }

        /**
         * 3D rotation initialize
         */
        function initialize3DRotation() {
            var container = $("#" + options.container);
            container.css('position', 'relative');
            options.rotation_trasition = getPrefix('transition');
            options.rotation_scale = getPrefix('transform');

            if(options.rotation_scale !== undefined){
                options.scale = options.rotation_scale;
            }

            options.elements.each(function(index){  addElementToRotation(options.elements[index]); });

            options.center.x = options.center.x + options.center_offset.x;
            options.center.y = options.center.y + options.center_offset.y + options.radius.y;

            reset3DRotation();
        }

        function reset3DRotation(){
            options.elements.each(function(index, elt){
                $(elt).animate({}, {
                    duration: options.fx.duration,
                    complete: function(){
                        options.current = index;
                        $("#" + options.container).trigger("complete");
                    }
                });
            });
            reorder3DRotation();
        }

        function reorder3DRotation(){
            options.elements.each(function (index, elt) {
                set3DStyles(elt, index);
            });
        }


        function set3DStyles(elt, index) {
            elt = $(elt);
            var theta = getRotationAngle(index),
                coefficient = 1 + (1 - options.minimal_ratio)/2 * (Math.cos(theta) - 1),
                width = elt.width() * coefficient;

            elt.css('position', 'absolute');
            elt.css('zIndex', parseInt(options.z_index + coefficient * 2 * options.elements.length));
            elt.css('left', (options.center.x + (options.radius.x * Math.sin(theta + options.offset_angle) + width/2)) + 'px');
            elt.css('top', (options.center.y + (options.radius.y * Math.cos(theta + options.offset_angle))) + 'px');

            if(options.rotation_scale !== undefined){
                elt.css('transform', 'scale(' + coefficient + ', ' + coefficient + ')');
                elt.css('-webkit-transform', 'scale(' + coefficient + ', ' + coefficient + ')');
                elt.css('-moz-transform', 'scale(' + coefficient + ', ' + coefficient + ')');
                elt.css('-ms-transform', 'scale(' + coefficient + ', ' + coefficient + ')');
                elt.css('-o-transform', 'scale(' + coefficient + ', ' + coefficient + ')');
            }
        }

        function getRotationAngle(index){
            var theta = index * 2 * Math.PI / options.elements.length;
            theta = ((theta + Math.PI) % (2 * Math.PI)) - Math.PI;
            return ((theta > 0 ? 1 : -1) * Math.PI * Math.pow(Math.abs(theta)/Math.PI, options.power_exponent));
        }

        function addElementToRotation(elt) {
            //var width, height;
            elt = jQuery(elt);
            elt.find("." + options.selector).css('display', 'block');
            elt.find("." + options.selector).css('opacity', 1);
            elt.find("." + options.selector).css('position', 'absolute');
            //width = elt.width();
            //height = elt.height();

            if(options.rotation_transition !== undefined){
                elt.css('transition', options.fx.duration + 'mw cubic-bezier(0.37, 0.01, 0.63, 1)');
            }
        }

        function getPrefix(prop){
            var len = options.prefixes.length,
                upper = prop.charAt(0).toUpperCase() + prop.slice(1),
                div = document.createElement('div');

            if(prop in div.style){
                return prop;
            }

            while(len--){
                if(options.prefixes[len] + upper in div.style){
                    return options.prefixes[len] + upper;
                }
            }
            return prop;
        }

        function updateSlider() {
            options.reverse ? previous() : next();
        }

        function initializeTimer() {
            options.running = false;
            registerTimerCallback();
        }

        function stopSlider() {
            stopTimer();
            options.active = false;
        }

        function startSlider() {
            registerTimerCallback();
            options.active = true;
        }

        function previous(direction){ alert("test progression"); return moveElements(options.current - options.distance, direction); }

        function next(direction){ return moveElements(options.current + options.distance, direction); }

        function moveElements(current, direction) {
            if(jQuery.type(current) === 'object' || jQuery.type(current) === 'string'){
                current = options.elements.index(current);
            }

            if(!options.circular){
                if(current > options.elements.length - options.scroll){
                    current = options.elements.length - options.scroll;
                }
                current = Math.max(current, 0);
            }else{
                if(current < 0){ current += options.elements.length; }
                current %= Math.max(options.elements.length, 1);
            }

            if(current < 0 || options.elements.length <= options.scroll || current > options.elements.length){

            }else{
                if(direction === undefined){
                    var forward = options.current < current ? current - options.current : options.elements.length - options.current;
                    var backWard = options.current > current ? options.current - current : options.current + options.elements.length;
                    direction = (Math.abs(forward) <= Math.abs(backWard) ? 1 : -1);
                }

                if(options.animation === 'Move'){

                }else if(options.animation === 'Rotate3D'){
                    move3DRotation(current, direction);
                }
            }
        }

        function move3DRotation(current) {
            var eltLength = options.elements.length, modulo;
            jQuery.map(options.elements, function (elt, index) {
                modulo = (eltLength + index - current) % eltLength;
                set3DStyles(elt, modulo);
            });
            options.current = current;
            carouselObj.trigger("change");
        }

        function resetRotateSize(){
            options.rotate_size.x = options.rotate_size.y + 2 * (options.margin + options.radius.x);
            options.rotate_size.y = options.rotate_size.y + 2 * (options.margin + options.radius.y);
        }

        function registerTimerCallback() {
            //startTimer();
            options.timer = setInterval(onTimerEvent, options.interval * 1000);
        }

        function startTimer(){

        }

        function stopTimer() {
            if(options.timer !== undefined && options.timer !== null){
                clearInterval(options.timer);
                options.timer = null;
            }
        }

        function onTimerEvent() {
            if(!options.running){
                try{
                    options.running = true;
                    updateSlider();
                }finally {
                    options.running = false;
                }
            }
        }
    }
})(jQuery);