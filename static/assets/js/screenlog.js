(function () {
	var logEl,
		isInitialized = false,
		_console = {};

	function createElement(tag, css) {
		var element = document.createElement(tag);
		element.style.cssText = css;
		element.id = "console_box"
		return element;
	}

	function createPanel(options) {
		options.bgColor = options.bgColor || 'black';
		options.color = options.color || 'lightgreen';
		options.css = options.css || '';
		var div = createElement('div', 'font-family: Helvetica, Arial, sans-serif;font-size: 10px;font-weight: bold;padding: 0 5px 0 5px;text-align: left;opacity: 0.8;		 position: fixed;bottom: 0px;overflow: auto;height: 30px;width: 80%;left: 0;right: 0;margin-left:auto;margin-right:auto;color: #7e57c2;');//background:' + options.bgColor + ';color:' + options.color + ';' + options.css
		return div;
	}

	function log() {
		// var el = createElement('div', 'line-height:18px;background:' +
		// 	(logEl.children.length % 2 ? 'rgba(255,255,255,0.1)' : ''));
		var el = createElement('div', 'line-height:18px;');
		var val = [].slice.call(arguments).reduce(function (prev, arg) {
			return prev + ' ' + arg;
		}, '');
		el.textContent = val;
		logEl.appendChild(el);
		logEl.scrollTop = logEl.scrollHeight - logEl.clientHeight;
	}

	function clear() {
		logEl.innerHTML = '';
	}

	function init(options) {
		if (isInitialized) { return; }
		isInitialized = true;
		options = options || {};
		logEl = createPanel(options);
		document.body.appendChild(logEl);

		if (!options.freeConsole) {
			_console.log = console.log;
			_console.clear = console.clear;
			console.log = originalFnCallDecorator(log, 'log');
			console.clear = originalFnCallDecorator(clear, 'clear');
		}
	}

	function destroy() {
		isInitialized = false;
		console.log = _console.log;
		console.clear = _console.clear;
		logEl.remove();
	}

	function checkInitialized() {
		if (!isInitialized) {
			throw 'You need to call `screenLog.init()` first.';
		}
	}

	function checkInitDecorator(fn) {
		return function () {
			checkInitialized();
			return fn.apply(this, arguments);
		};
	}

	function originalFnCallDecorator(fn, fnName) {
		return function () {
			fn.apply(this, arguments);
			if (typeof _console[fnName] === 'function') {
				_console[fnName].apply(console, arguments);
			}
		};
	}

	window.screenLog = {
		init: init,
		log: originalFnCallDecorator(checkInitDecorator(log), 'log'),
		clear: originalFnCallDecorator(checkInitDecorator(clear), 'clear'),
		destroy: checkInitDecorator(destroy)
	};
})();