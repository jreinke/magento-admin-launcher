BubbleLauncher = Class.create({
    initialize: function(options) {
        this.options = Object.extend({
            minChars: 2,
            maxResults: 10,
            hotkey: 32, // space by default
            dataLocation: '',
            resetOnHide: false,
            useScope: true,
            showIcon: true,
            showScope: true,
            showText: true,
            delay: 200,
            onReady: function() {},
            onError: function() {}
        }, options || {});

        if (!this.options.dataLocation) {
            this.error('You must specify data location url.');
            return;
        }

        if (this.options.hotkey == Event.KEY_DOWN || this.options.hotkey == Event.KEY_UP) {
            this.error('You cannot define up/down key as hotkey.');
            return;
        }

        if (this.options.hotkey == Event.ESC) {
            this.error('You cannot define escape key as hotkey.');
            return;
        }

        if (this.options.hotkey == Event.KEY_RETURN) {
            this.error('You cannot define return key as hotkey.');
            return;
        }

        if (this.options.hotkey == Event.KEY_TAB) {
            this.error('You cannot define tab key as hotkey.');
            return;
        }

        this.active = false;
        this.pages = {};
        this.scopes = [];
        this.state = '';
        this.timeout = null;

        // Container init
        $$('body')[0].insert(
            '<div id="bubblelauncher-container">' +
                '<div id="bubblelauncher-field">' +
                    '<input type="text" id="bubblelauncher-suggester" readonly disabled />' +
                    '<input type="text" id="bubblelauncher-input" />' +
                '</div>' +
                '<div id="bubblelauncher-results"></div>' +
            '</div>'
        );

        this.container = $('bubblelauncher-container');
        this.input = $('bubblelauncher-input');
        this.results = $('bubblelauncher-results');
        this.suggester = $('bubblelauncher-suggester');

        // Retrieve JSON data
        var self = this;
        new Ajax.Request(this.options.dataLocation, {
            method:'get',
            loaderArea: false,
            onComplete: function(transport) {
                // Retrieve index data
                var response = transport.responseJSON;

                // Init scopes and pages
                self.scopes = response.scopes;
                self.pages = response.pages;
                if (self.pages.length > 0) {
                    self.ready();
                } else {
                    self.error('No page found in response.');
                }
            }
        });
    },
    error: function(error) {
        console.error(error);
        this.options.onError.call(this, error);
    },
    ready: function() {
        document.observe('keydown', this.onKeyDown.bindAsEventListener(this));
        document.observe('click', this.onClick.bindAsEventListener(this));
        document.observe('keyup', this.onKeyUp.bindAsEventListener(this));
        this.options.onReady.call(this);
    },
    toggle: function() {
        this.active ? this.hide() : this.show();
    },
    show: function(event) {
        if (event) {
            Event.stop(event);
        }
        this.container.setStyle({ display: 'block' });
        this.input.focus();
        this.active = true;
    },
    hide: function(force) {
        if (!this.active || (force === undefined && document.activeElement == this.input)) {
            return;
        }
        this.active = false;
        this.container.setStyle({ display: 'none' });
        if (this.options.resetOnHide) {
            this.reset();
        }
        this.input.blur();
    },
    reset: function() {
        this.setQuery('');
        this.emptyResults();
        this.active = false;
    },
    markResult: function(direction) {
        var items = $$('#bubblelauncher-results li.selected');
        if (!items.length) {
            return;
        }
        var isUp = (direction === 'up');
        var element = items[0].removeClassName('selected');
        var next = isUp ? element.previous() : element.next();
        if (next) {
            next.addClassName('selected');
        } else {
            var pseudo = isUp ? 'last' : 'first';
            $$('#bubblelauncher-results li:' + pseudo)[0].addClassName('selected');
        }
    },
    markPrevious: function() {
        this.markResult('up');
    },
    markNext: function() {
        this.markResult('down');
    },
    emptyResults: function() {
        this.results.setStyle({ display: 'none' }).innerHTML = '';
    },
    getScope: function() {
        if (this.options.useScope) {
            var scopes = Object.values(this.scopes);
            var q = this.getQuery();
            if (q.indexOf(': ') > 0) {
                var wantedScope = q.substr(0, q.indexOf(': '));
                for (var i = 0; i < scopes.length; i++) {
                    if (scopes[i] === wantedScope) {
                        return this.getScopeByTranslation(scopes[i]);
                    }
                }
            }
        }

        return null;
    },
    getScopeTranslation: function(scope) {
        for (var key in this.scopes) {
            if (key === scope) {
                return this.scopes[key];
            }
        }

        return scope;
    },
    getScopeByTranslation: function(scope) {
        for (var key in this.scopes) {
            if (scope === this.scopes[key]) {
                return key;
            }
        }

        return scope;
    },
    getQuery: function() {
        return this.input.value;
    },
    getSuggestion: function() {
        return this.suggester.value;
    },
    getScore: function(str, q) {
        if (typeof str !== 'string' || typeof q !== 'string') {
            return 0;
        }
        str = str.trim().toLowerCase();
        q = q.trim().toLowerCase();
        var score = str.score(q);
        if (str.indexOf(q) !== -1) {
            score *= 1.1; // boost exact word match in string
        }

        return score;
    },
    setQuery: function(str) {
        this.input.value = str;
    },
    setSuggestion: function(str) {
        this.suggester.value = str;
    },
    suggest: function() {
        if (this.options.useScope) {
            var scopes = Object.values(this.scopes);
            var q = this.getQuery();
            if (q.length > 0) {
                for (var i = 0; i < scopes.length; i++) {
                    if (scopes[i].indexOf(q) === 0) {
                        this.setSuggestion(scopes[i]);
                        return;
                    }
                }
            }
            this.setSuggestion('');
        }
    },
    scopeQuery: function() {
        if (this.options.useScope) {
            var q = this.getQuery();
            var suggest = this.getSuggestion();
            if (q.length > 0 && suggest.indexOf(q) === 0) {
                this.setQuery(suggest + ': ');
                this.setSuggestion('');
                this.emptyResults();
            }
        }
    },
    delaySearch: function() {
        if (this.options.delay <= 0) {
            this.search();
        } else {
            if (this.timeout) {
                clearTimeout(this.timeout);
            }
            this.timeout = setTimeout(function() {
                this.search();
            }.bind(this), this.options.delay);
        }
    },
    search: function() {
        var q = this.getQuery();

        if (q === this.state) {
            return;
        }

        this.state = q;
        var scope = this.getScope();

        if (scope) {
            q = q.substr(q.indexOf(': ') + 2);
        }

        if (!scope && q.length < this.options.minChars) {
            this.emptyResults();
        } else {
            this.pages.sort(function(obj1, obj2) {
                var score1 = (!scope || obj1.scope === scope) ? this.getScore(obj1.title, q) : 0;
                var score2 = (!scope || obj2.scope === scope) ? this.getScore(obj2.title, q) : 0;

                if (score1 !== score2) {
                    return score2 - score1;
                } else if (obj1.title < obj2.title) {
                    return -1;
                } else if (obj1.title > obj2.title) {
                    return 1;
                }

                return 0;
            }.bind(this));

            var count = 0;
            var out = '<ul' + (this.options.showIcon ? ' class="show-icon"' : '') + '>';
            for (var j = 0; j < this.pages.length; j++) {
                if (scope && this.pages[j].scope !== scope) {
                    continue;
                }
                var showScope = (!scope && this.options.showScope); // force hidden scope when scope is active
                var className = this.pages[j].scope;
                if (count === 0) {
                    className += ' selected';
                }
                out += '<li class="' + className + '"><a href="' + this.pages[j].url + '">' +
                    (showScope ? '<span class="scope">[' + this.getScopeTranslation(this.pages[j].scope).capitalize() + ']</span> ' : '') +
                    this.pages[j].title +
                    (this.options.showText ? '<br /><span class="text">' + ' ' + this.pages[j].text + '</span>' : '') +
                    '</li>';
                count++;
                if (count == this.options.maxResults) {
                    break;
                }
            }
            out += '</ul>';
            if (count > 0) {
                this.results.setStyle({ display: 'block' }).innerHTML = out;
            } else {
                this.emptyResults();
            }
        }
    },
    onClick: function(event) {
        if (this.active) {
            var element = event.element();
            if (element !== this.container && undefined === element.up('#' + this.container.id)) {
                this.hide();
            }
        }
    },
    onKeyDown: function(event) {
        var element = event.element();

        if (element == this.input) {
            switch (event.keyCode) {
                case Event.KEY_DOWN:
                    Event.stop(event);
                    this.markNext();
                    return;
                case Event.KEY_UP:
                    Event.stop(event);
                    this.markPrevious();
                    return;
                case Event.KEY_RETURN:
                    Event.stop(event);
                    var link = $$('#bubblelauncher-results li.selected a')[0];
                    if (link) {
                        document.location.href = link.href;
                    }
                    return;
                case Event.KEY_TAB:
                    this.scopeQuery();
                    this.delaySearch();
                    Event.stop(event);
                    return;
            }
        }

        if (element.tagName == 'INPUT' || element.tagName == 'TEXTAREA') {
            return;
        }

        if (event.keyCode == this.options.hotkey) {
            this.toggle();
            Event.stop(event);
        }
    },
    onKeyUp: function(event) {
        if (!this.active) {
            return;
        }

        switch(event.keyCode) {
            case Event.KEY_ESC:
                this.hide(true);
                Event.stop(event);
                return;
            case Event.KEY_DOWN:
            case Event.KEY_UP:
            case Event.KEY_RIGHT:
            case Event.KEY_LEFT:
            case Event.KEY_RETURN:
            case Event.KEY_TAB:
            case 91: // Mac cmd
                return;
        }

        this.suggest();
        this.delaySearch();
    }
});
if (!String.prototype.trim) {
    String.prototype.trim = function() {
        return this.replace(/^\s+|\s+$/g, '');
    }
}