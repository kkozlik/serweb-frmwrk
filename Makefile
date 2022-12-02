NAME	= serweb-frmwrk

BASE_DIR	= ${NAME}

# framework files to deploy
FILES	= $(shell cd ${NAME} ; find . -type f | grep -v /config/)
# FILES	= $(shell $(wildcard serweb-frmwrk/*) | grep -v /config/)
# documentation to deploy
FILES_DOC	= \
	TODO.txt \
	INSTALL.txt \
	COPYING \
	CHANGELOG
FILES_EXAMPLE = $(shell find	example-app -type f)
# configuration to deploy
FILES_ETC	= $(shell cd ${NAME}/config ; find . -type f)

DEST_SERWEB	= $(DESTDIR)/usr/share/serweb-frmwrk
DEST_SERWEB_DOC	= $(DESTDIR)/usr/share/doc/serweb-frmwrk
DEST_SERWEB_ETC	= $(DESTDIR)/etc/serweb-frmwrk

INSTALL_DEPS = \
	$(addprefix $(DEST_SERWEB)/,$(FILES)) \
	$(addprefix $(DEST_SERWEB_DOC)/,$(FILES_DOC)) \
	$(addprefix $(DEST_SERWEB_DOC)/,$(FILES_EXAMPLE)) \
	$(DEST_SERWEB_DOC)/example-app/pages/js/core \
	$(DEST_SERWEB_DOC)/example-app/pages/styles/core \
	$(addprefix $(DEST_SERWEB_ETC)/,$(FILES_ETC)) \
	$(DESTDIR)/usr/share/serweb-frmwrk/config

$(DEST_SERWEB)/%:	${NAME}/% ## Deploy framework files to destdir (% subtitute to ${FILES})
	install -m 0644 -D $< $@

$(DEST_SERWEB_DOC)/%:	% ## Deploy doc files to destidr (% subtitute to ${FILES_DOC} and ${FILES_EXAMPLE})
	install -m 0644 -D $< $@

$(DEST_SERWEB_ETC)/%:	${NAME}/config/% ## Deploy config files to destidr (% subtitute to ${FILES_ETC})
	install -m 0644 -D $< $@

$(DEST_SERWEB_DOC)/example-app/pages/%/core: ## Symlink framework example cores into destdir
	mkdir -p $(dir $@)
	ln -sf ../../../serweb-frmwrk/pages/$*/ $@

$(DESTDIR)/usr/share/serweb-frmwrk/config: ## Symlink framework config into destdir
	ln -sf /etc/serweb-frmwrk $@

install_ccm: ${INSTALL_DEPS}
