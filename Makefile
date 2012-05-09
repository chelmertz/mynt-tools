OUTPUT = _build
TITLE = new
define TEMPLATE
---
layout: post.html
title: "$(TITLE)"
---

endef
export TEMPLATE

clean:
	rm -rf $(OUTPUT)

new:
	# mvim is macvim, at least vim is needed for these exact parameters
	echo "$$TEMPLATE" | mvim - +"file _posts/$$(date "+%Y-%m-%d-%H-%M")-$(TITLE).md"

edit:
	# edit the last modified file, usually the most recently created
	mvim _posts/$$(ls -t _posts | head -n 1)

deploy: generate
	rsync -zavrR --delete --rsh "ssh -l *user-here*" $(OUTPUT) *destination here*

generate: clean
	mynt gen $(OUTPUT)
	cp robots.txt $(OUTPUT)

test: generate
	open http://127.0.0.1:8080
	@echo "***"
	@echo "Just reload the browser in a few seconds"
	@echo "***"
	mynt serve $(OUTPUT)
