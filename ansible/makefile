#VAULT_PASSWORD_OPTION=--vault-password-file=.vault_password
ANSIBLE_OPTIONS= #$(VAULT_PASSWORD_OPTION)
ANSIBLE_PLAYBOOK=ansible-playbook $(ANSIBLE_OPTIONS)

.PHONY: install
install:
	$(ANSIBLE_PLAYBOOK) \
	    -i hosts.yaml \
	    install.yaml

.PHONY: install-local
install-local:
	$(ANSIBLE_PLAYBOOK) \
	    -i hosts.local.yaml \
	    install.yaml

.PHONY: deploy-develop
deploy-develop:
	$(ANSIBLE_PLAYBOOK) \
	    -i hosts.yaml \
	    -e deploy_branch=develop \
	    deploy.yaml

.PHONY: deploy-master
deploy-master:
	$(ANSIBLE_PLAYBOOK) \
	    -i hosts.yaml \
	    -e deploy_branch=master \
	    deploy.yaml
