
GENERATED_FILES = \
	dw.js/dist/dw-2.0.js \
	www/static/js/dw-2.0.min.js

all: $(GENERATED_FILES)

clean:
	@grunt clean

dw.js/dist/dw-2.0.js: dw.js/src/*.js
	@grunt dwjs

www/static/js/dw-2.0.min.js: dw.js/dist/dw-2.0.js
	@grunt assets

messages:
	scripts/update-messages.sh
