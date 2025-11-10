'use strict';

(function () {
    document.addEventListener('DOMContentLoaded', function () {
        initializePreviewHandlers();
        initializeSortableLists();
        initializeHeroStatsManager();
        initializeFeaturedWorkManager();
        initializeTestimonialsManager();
        initializeHomePageCtaManager();
        initializeServicesPreviewManager();
        initializeSkillsPreviewManager();
        initializeAboutHighlights();
        initializeTimelineForm();
        initializeTimelineManager();
    });
})();

const toggleRegistry = new WeakMap();
const heroStatItemTimers = new WeakMap();

function initializePreviewHandlers() {
    document.querySelectorAll('.admin-form').forEach(registerPreviewContainer);
}

function registerPreviewContainer(container) {
    if (!container || container.dataset.previewBound === 'true') {
        return;
    }

    container.dataset.previewBound = 'true';

    bindTextPreviews(container);
    bindListPreviews(container);
    bindImagePreviews(container);
    bindFilePreviews(container);
    bindToggleContainers(container);
    bindFormCancel(container);
}

function bindFormCancel(container) {
    const cancelButton = container.querySelector('[data-form-cancel]');
    if (!cancelButton || cancelButton.dataset.cancelBound === 'true') {
        return;
    }

    cancelButton.dataset.cancelBound = 'true';

    cancelButton.addEventListener('click', function (event) {
        const form = cancelButton.closest('form');
        if (!form) {
            return;
        }

        event.preventDefault();
        form.reset();

        const fields = form.querySelectorAll('input, textarea, select');
        fields.forEach(function (field) {
            const eventName = determineEventName(field);
            field.dispatchEvent(new Event(eventName, { bubbles: true }));
        });

        resetPreviewMediaForForm(form);
    });
}

function resetPreviewMediaForForm(form) {
    if (!form) {
        return;
    }

    const urlInputs = form.querySelectorAll('[data-preview-image]');
    urlInputs.forEach(function (input) {
        const selectors = parseSelectors(input.dataset.previewImage);
        selectors.forEach(function (selector) {
            form.querySelectorAll(selector).forEach(restoreTargetSrc);
        });
    });

    const fileInputs = form.querySelectorAll('[data-preview-file]');
    fileInputs.forEach(function (input) {
        input.value = '';
        const selectors = parseSelectors(input.dataset.previewFile);
        selectors.forEach(function (selector) {
            form.querySelectorAll(selector).forEach(restoreTargetSrc);
        });
    });
}

function bindTextPreviews(form) {
    const fields = form.querySelectorAll('[data-preview-target]');
    const handledRadios = new Set();

    fields.forEach(function (field) {
        const eventName = determineEventName(field);
        const update = function () {
            updateFieldPreview(field, form);
        };

        field.addEventListener(eventName, update);

        if (field.type === 'radio') {
            // ensure radio groups update even when other option is selected
            const groupKey = `${field.form ? field.form.id : 'form'}::${field.name}`;
            if (!handledRadios.has(groupKey)) {
                handledRadios.add(groupKey);
                update();
            }
        } else {
            update();
        }
    });
}

function updateFieldPreview(field, form) {
    const selectors = parseSelectors(field.dataset.previewTarget);
    if (!selectors.length) {
        return;
    }

    const property = (field.dataset.previewProperty || 'text').toLowerCase();
    const toggleClass = field.dataset.previewToggleClass || '';
    const toggleSelector = field.dataset.previewToggleSelector || '';

    const payload = resolveFieldValue(field, form, property);

    selectors.forEach(function (selector) {
        const targets = form.querySelectorAll(selector);
        if (!targets.length) {
            return;
        }

        targets.forEach(function (target) {
            ensureTargetOriginal(target);
            if (payload.isEmpty) {
                restoreTarget(target);
            } else if (payload.isHtml || property === 'html') {
                target.innerHTML = payload.value;
            } else {
                target.textContent = payload.value;
            }
        });

        if (toggleClass) {
            const toggleTargets = toggleSelector ? form.querySelectorAll(toggleSelector) : targets;
            toggleTargets.forEach(function (toggleTarget) {
                applyToggle(toggleTarget, getToggleReason(field), payload.isEmpty, toggleClass);
            });
        }
    });
}

function resolveFieldValue(field, form, property) {
    let source = field;

    if (field.type === 'radio') {
        const checked = form.querySelector(`input[type="radio"][name="${escapeSelector(field.name)}"]:checked`);
        if (!checked) {
            return { value: '', isHtml: property === 'html', isEmpty: true };
        }
        source = checked;
    }

    if (field.type === 'checkbox') {
        if (!field.checked) {
            return { value: '', isHtml: false, isEmpty: true };
        }
    }

    const htmlTemplate = source.dataset.previewHtml;
    if (typeof htmlTemplate === 'string' && htmlTemplate.length) {
        return {
            value: htmlTemplate,
            isHtml: true,
            isEmpty: htmlTemplate.trim() === ''
        };
    }

    if (field.type === 'checkbox') {
        const templateValue = source.dataset.previewValue || source.value || 'On';
        return {
            value: templateValue,
            isHtml: property === 'html',
            isEmpty: templateValue.trim() === ''
        };
    }

    let rawValue = source.value || '';
    rawValue = rawValue.trim();

    if (property === 'html') {
        return {
            value: textToHtml(rawValue),
            isHtml: true,
            isEmpty: rawValue === ''
        };
    }

    const templateValue = source.dataset.previewValue;
    if (templateValue && source.type === 'radio') {
        return {
            value: templateValue,
            isHtml: false,
            isEmpty: templateValue.trim() === ''
        };
    }

    return {
        value: rawValue,
        isHtml: false,
        isEmpty: rawValue === ''
    };
}

function bindListPreviews(form) {
    const inputs = form.querySelectorAll('[data-preview-list]');
    inputs.forEach(function (input) {
        const selectors = parseSelectors(input.dataset.previewList);
        if (!selectors.length) {
            return;
        }

        const eventName = determineEventName(input);
        const update = function () {
            const items = parseListInput(input);
            const type = input.dataset.previewListType || 'badge';
            const limit = input.dataset.previewListLimit ? parseInt(input.dataset.previewListLimit, 10) : null;

            selectors.forEach(function (selector) {
                const targets = form.querySelectorAll(selector);
                targets.forEach(function (target) {
                    ensureTargetOriginal(target);
                    renderListPreview(target, items, type, limit);
                });
            });
        };

        input.addEventListener(eventName, update);
        update();
    });
}

function bindImagePreviews(form) {
    const urlInputs = form.querySelectorAll('[data-preview-image]');
    urlInputs.forEach(function (input) {
        const selectors = parseSelectors(input.dataset.previewImage);
        if (!selectors.length) {
            return;
        }

        const update = function () {
            const value = (input.value || '').trim();
            selectors.forEach(function (selector) {
                const targets = form.querySelectorAll(selector);
                targets.forEach(function (target) {
                    ensureTargetOriginalSrc(target);
                    if (value) {
                        target.setAttribute('src', value);
                    } else {
                        restoreTargetSrc(target);
                    }
                });
            });
        };

        input.addEventListener('input', update);
        update();
    });
}

function bindFilePreviews(form) {
    const fileInputs = form.querySelectorAll('[data-preview-file]');
    fileInputs.forEach(function (input) {
        const selectors = parseSelectors(input.dataset.previewFile);
        if (!selectors.length) {
            return;
        }

        input.addEventListener('change', function () {
            if (!input.files || !input.files.length) {
                selectors.forEach(function (selector) {
                    const targets = form.querySelectorAll(selector);
                    targets.forEach(restoreTargetSrc);
                });
                return;
            }

            const file = input.files[0];
            if (!file) {
                selectors.forEach(function (selector) {
                    const targets = form.querySelectorAll(selector);
                    targets.forEach(restoreTargetSrc);
                });
                return;
            }

            const reader = new FileReader();
            reader.onload = function (event) {
                const result = event.target && event.target.result ? event.target.result.toString() : '';
                if (!result) {
                    return;
                }

                selectors.forEach(function (selector) {
                    const targets = form.querySelectorAll(selector);
                    targets.forEach(function (target) {
                        target.setAttribute('src', result);
                    });
                });
            };
            reader.readAsDataURL(file);
        });
    });
}

function bindToggleContainers(form) {
    const containers = form.querySelectorAll('[data-preview-toggle]');
    containers.forEach(function (container) {
        const selector = container.dataset.toggleSelector;
        if (!selector) {
            return;
        }

        const className = container.dataset.toggleClass || 'd-none';
        const hideValues = (container.dataset.toggleHideValue || 'no,false,hidden').split(',').map(function (value) {
            return value.trim().toLowerCase();
        });

        const watchers = form.querySelectorAll(selector);
        if (!watchers.length) {
            return;
        }

        const evaluate = function () {
            let shouldHide = false;

            watchers.forEach(function (watcher) {
                if (shouldHide) {
                    return;
                }

                if (watcher.type === 'radio') {
                    const checked = form.querySelector(`input[type="radio"][name="${escapeSelector(watcher.name)}"]:checked`);
                    if (!checked) {
                        shouldHide = true;
                        return;
                    }
                    const rawValue = (checked.dataset.previewValue || checked.value || '').toLowerCase();
                    shouldHide = hideValues.includes(rawValue);
                } else if (watcher.type === 'checkbox') {
                    const hideWhenChecked = container.dataset.toggleHideWhenChecked === 'true';
                    shouldHide = hideWhenChecked ? watcher.checked : !watcher.checked;
                } else {
                    shouldHide = (watcher.value || '').trim() === '';
                }
            });

            applyToggle(container, getToggleReason(container), shouldHide, className);
        };

        watchers.forEach(function (watcher) {
            const eventName = determineEventName(watcher);
            watcher.addEventListener(eventName, evaluate);
        });

        evaluate();
    });
}

function determineEventName(element) {
    if (element.type === 'file') {
        return 'change';
    }
    if (element.type === 'checkbox' || element.type === 'radio' || element.tagName === 'SELECT') {
        return 'change';
    }
    return 'input';
}

function parseSelectors(rawSelectors) {
    if (!rawSelectors) {
        return [];
    }
    return rawSelectors.split(',').map(function (selector) {
        return selector.trim();
    }).filter(Boolean);
}

function ensureTargetOriginal(target) {
    if (typeof target.dataset.previewOriginal === 'undefined') {
        target.dataset.previewOriginal = target.innerHTML;
    }
}

function ensureTargetOriginalSrc(target) {
    if (typeof target.dataset.previewOriginalSrc === 'undefined') {
        target.dataset.previewOriginalSrc = target.getAttribute('src') || '';
    }
}

function restoreTarget(target) {
    if (typeof target.dataset.previewOriginal !== 'undefined') {
        target.innerHTML = target.dataset.previewOriginal;
    } else {
        target.textContent = '';
    }
}

function restoreTargetSrc(target) {
    ensureTargetOriginalSrc(target);
    const fallback = target.dataset.previewOriginalSrc;
    if (typeof fallback === 'string') {
        target.setAttribute('src', fallback);
    }
}

function parseListInput(input) {
    const rawValue = input.value || '';
    const delimiter = input.dataset.previewDelimiter || (input.tagName === 'TEXTAREA' ? 'newline' : 'comma');
    let items = [];

    if (delimiter === 'newline') {
        items = rawValue.split(/\r?\n/);
    } else {
        items = rawValue.split(',');
    }

    return items
        .map(function (item) { return item.trim(); })
        .filter(function (item) { return item.length > 0; });
}

function initializeSortableLists() {
    const lists = document.querySelectorAll('[data-sortable-list]');
    lists.forEach(function (list) {
        if (list.dataset.sortableInitialised === 'true') {
            return;
        }
        list.dataset.sortableInitialised = 'true';
        setupSortableList(list);
    });
}

function setupSortableList(list) {
    const orderInputSelector = list.dataset.sortableOrderInput || '';
    const saveButtonSelector = list.dataset.sortableSaveButton || '';

    const orderInput = orderInputSelector ? document.querySelector(orderInputSelector) : null;
    const saveButton = saveButtonSelector ? document.querySelector(saveButtonSelector) : null;

    const initialOrder = serializeListOrder(list);
    if (orderInput && !orderInput.value) {
        orderInput.value = initialOrder;
    }

    let referenceOrder = orderInput ? orderInput.value : initialOrder;
    if (orderInput && typeof orderInput.dataset.sortableReference === 'undefined') {
        orderInput.dataset.sortableReference = referenceOrder;
    }
    let dragSource = null;
    let dragHandleEngaged = false;
    let dragHandleTarget = null;

    list.addEventListener('mousedown', function (event) {
        const handle = event.target.closest('[data-sortable-handle]');
        if (handle && list.contains(handle)) {
            dragHandleEngaged = true;
            dragHandleTarget = handle;
        }
    });

    list.addEventListener('mouseup', function () {
        dragHandleEngaged = false;
        dragHandleTarget = null;
    });

    window.addEventListener('mouseup', function () {
        dragHandleEngaged = false;
        dragHandleTarget = null;
    });

    function applyDraggableAttributes(targetList, nodes) {
        const items = nodes
            ? Array.from(nodes).filter(function (node) {
                return node && node.nodeType === Node.ELEMENT_NODE;
            })
            : Array.from(targetList.querySelectorAll('[data-sortable-item]'));

        items.forEach(function (item) {
            if (!(item instanceof Element)) {
                return;
            }
            if (item.matches('[data-sortable-item]')) {
                item.setAttribute('draggable', 'true');
            }
        });
    }

    applyDraggableAttributes(list);

    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            if (!mutation.addedNodes || mutation.addedNodes.length === 0) {
                return;
            }

            const added = [];
            mutation.addedNodes.forEach(function (node) {
                if (!node || node.nodeType !== Node.ELEMENT_NODE) {
                    return;
                }

                if (node.matches && node.matches('[data-sortable-item]')) {
                    added.push(node);
                }

                if (node.querySelectorAll) {
                    node.querySelectorAll('[data-sortable-item]').forEach(function (child) {
                        added.push(child);
                    });
                }
            });

            if (added.length) {
                applyDraggableAttributes(list, added);
            }
        });
    });

    observer.observe(list, { childList: true });

    list.addEventListener('dragstart', function (event) {
        if (!dragHandleEngaged) {
            event.preventDefault();
            dragSource = null;
            return;
        }

        const origin = dragHandleTarget || event.target;
        const item = getSortableItem(origin);
        if (!item) {
            event.preventDefault();
            return;
        }

        dragSource = item;
        dragHandleEngaged = false;
        dragHandleTarget = null;
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', item.dataset.sortableId || '');
        item.classList.add('is-dragging');
    });

    list.addEventListener('dragend', function (event) {
        const item = getSortableItem(event.target);
        if (item) {
            item.classList.remove('is-dragging');
        }
        dragSource = null;
        syncOrder();
        list.dispatchEvent(new CustomEvent('sortable:reordered', { bubbles: true })); // notify listeners such as previews
    }, true);

    list.addEventListener('dragover', function (event) {
        if (!dragSource) {
            return;
        }

        event.preventDefault();

        const targetItem = getSortableItem(event.target);
        if (!targetItem || targetItem === dragSource) {
            return;
        }

        const rect = targetItem.getBoundingClientRect();
        const offset = event.clientY - rect.top;
        const shouldInsertAfter = offset > rect.height / 2;

        list.insertBefore(dragSource, shouldInsertAfter ? targetItem.nextSibling : targetItem);
    });

    list.addEventListener('drop', function (event) {
        if (!dragSource) {
            return;
        }
        event.preventDefault();
        syncOrder();
        list.dispatchEvent(new CustomEvent('sortable:reordered', { bubbles: true }));
    });

    function syncOrder() {
        if (!orderInput) {
            return;
        }

        const serialized = serializeListOrder(list);
        orderInput.value = serialized;

        if (orderInput && typeof orderInput.dataset.sortableReference === 'string') {
            if (orderInput.dataset.sortableReference !== referenceOrder) {
                referenceOrder = orderInput.dataset.sortableReference;
            }
        }

        if (saveButton) {
            saveButton.disabled = serialized === referenceOrder;
        }
    }

    syncOrder();
}

function serializeListOrder(list) {
    return Array.from(list.querySelectorAll('[data-sortable-item]'))
        .map(function (item) {
            return item.dataset.sortableId || '';
        })
        .filter(Boolean)
        .join(',');
}

function getSortableItem(element) {
    if (!element) {
        return null;
    }
    return element.closest('[data-sortable-item]');
}

function initializeHeroStatsManager() {
    const manager = document.querySelector('[data-hero-stats-manager]');
    if (!manager) {
        return;
    }

    const list = manager.querySelector('[data-hero-stats-list]');
    const template = manager.querySelector('#heroStatItemTemplate');
    const addForm = manager.querySelector('[data-hero-stat-add-form]');
    const addButton = manager.querySelector('[data-hero-stat-add-button]');
    const addLabelInput = manager.querySelector('[data-hero-stat-add-label]');
    const addValueInput = manager.querySelector('[data-hero-stat-add-value]');
    const addVisibleInput = manager.querySelector('[data-hero-stat-add-visible]');
    const feedback = manager.querySelector('[data-hero-stats-feedback]');
    const emptyState = manager.querySelector('[data-hero-stats-empty]');
    const countBadge = document.querySelector('[data-hero-stats-count]');
    const previewContainer = document.querySelector('[data-hero-stats-preview]');

    if (!list || !template) {
        return;
    }

    const limit = parseInt(manager.dataset.heroStatsLimit, 10) || 3;
    const createUrl = manager.dataset.heroStatsCreateUrl || '';
    const reorderUrl = manager.dataset.heroStatsReorderUrl || '';
    const initialData = parseHeroStatsInitial(manager.dataset.heroStatsInitial);

    const templateContent = template.content && template.content.firstElementChild
        ? template.content.firstElementChild
        : null;

    if (!templateContent) {
        return;
    }

    const state = {
        reorderController: null,
        feedbackTimer: null
    };

    function parseHeroStatsInitial(raw) {
        if (!raw) {
            return [];
        }

        try {
            const parsed = JSON.parse(raw);
            if (Array.isArray(parsed)) {
                return parsed;
            }
        } catch (error) {
            console.warn('Failed to parse hero stats dataset', error);
        }

        return [];
    }

    function setFeedback(message, tone, key) {
        if (!feedback) {
            return;
        }

        if (state.feedbackTimer) {
            window.clearTimeout(state.feedbackTimer);
            state.feedbackTimer = null;
        }

        feedback.textContent = message || '';

        if (typeof key === 'string') {
            feedback.dataset.feedbackKey = key;
        }

        feedback.classList.remove('d-none', 'text-danger', 'text-success', 'text-warning', 'text-muted');

        if (!message) {
            feedback.classList.add('d-none');
            delete feedback.dataset.feedbackKey;
            return;
        }

        switch (tone) {
            case 'error':
                feedback.classList.add('text-danger');
                break;
            case 'success':
                feedback.classList.add('text-success');
                break;
            case 'warning':
                feedback.classList.add('text-warning');
                break;
            default:
                feedback.classList.add('text-muted');
        }

        if (tone === 'success') {
            const appliedKey = feedback.dataset.feedbackKey || null;

            state.feedbackTimer = window.setTimeout(function () {
                if (appliedKey && feedback.dataset.feedbackKey && feedback.dataset.feedbackKey !== appliedKey) {
                    return;
                }

                feedback.textContent = '';
                feedback.classList.add('d-none');
                feedback.classList.remove('text-danger', 'text-success', 'text-warning', 'text-muted');
                if (feedback.dataset.feedbackKey === appliedKey) {
                    delete feedback.dataset.feedbackKey;
                }
                state.feedbackTimer = null;
            }, 2500);
        }
    }

    function createStatElement(stat) {
        const element = templateContent.cloneNode(true);
        element.dataset.heroStatId = String(stat.id || '');
        element.dataset.sortableId = String(stat.id || '');
        element.dataset.updateUrl = stat.update_url || '';
        element.dataset.deleteUrl = stat.delete_url || '';
        element.dataset.sortOrder = String(stat.sort_order || 0);

        const labelInput = element.querySelector('[data-hero-stat-label]');
        const valueInput = element.querySelector('[data-hero-stat-value]');
        const visibleInput = element.querySelector('[data-hero-stat-visible]');

        if (labelInput) {
            labelInput.value = stat.label || '';
        }
        if (valueInput) {
            valueInput.value = stat.value || '';
        }
        if (visibleInput) {
            visibleInput.checked = String(stat.is_active || 0) === '1';
        }

        bindStatEvents(element);
        return element;
    }

    function bindStatEvents(item) {
        const labelInput = item.querySelector('[data-hero-stat-label]');
        const valueInput = item.querySelector('[data-hero-stat-value]');
        const visibleInput = item.querySelector('[data-hero-stat-visible]');
        const saveButton = item.querySelector('[data-hero-stat-save]');
        const deleteButton = item.querySelector('[data-hero-stat-delete]');

        if (labelInput) {
            labelInput.addEventListener('input', function () {
                markStatDirty(item);
                refreshPreview();
            });
        }

        if (valueInput) {
            valueInput.addEventListener('input', function () {
                markStatDirty(item);
                refreshPreview();
            });
        }

        if (visibleInput) {
            visibleInput.addEventListener('change', function () {
                markStatDirty(item);
                refreshPreview();
            });
        }

        if (saveButton) {
            saveButton.addEventListener('click', function () {
                saveStat(item, saveButton);
            });
        }

        if (deleteButton) {
            deleteButton.addEventListener('click', function () {
                deleteStat(item, deleteButton);
            });
        }
    }

    function markStatDirty(item) {
        if (!item) {
            return;
        }
        item.dataset.dirty = 'true';
        setItemFeedback(item, 'Unsaved changes', 'warning');
    }

    function clearStatDirty(item) {
        if (!item) {
            return;
        }
        delete item.dataset.dirty;
    }

    function setItemFeedback(item, message, tone) {
        if (!item) {
            return;
        }

        const target = item.querySelector('[data-hero-stat-feedback]');
        if (!target) {
            return;
        }

        if (heroStatItemTimers.has(item)) {
            window.clearTimeout(heroStatItemTimers.get(item));
            heroStatItemTimers.delete(item);
        }

        target.textContent = message || '';
        target.classList.remove('text-danger', 'text-success', 'text-warning', 'text-muted', 'is-visible');

        if (!message) {
            return;
        }

        target.classList.add('is-visible');

        switch (tone) {
            case 'error':
                target.classList.add('text-danger');
                break;
            case 'success':
                target.classList.add('text-success');
                break;
            case 'warning':
                target.classList.add('text-warning');
                break;
            default:
                target.classList.add('text-muted');
        }

        if (tone === 'success') {
            const timerId = window.setTimeout(function () {
                target.textContent = '';
                target.classList.remove('text-danger', 'text-success', 'text-warning', 'text-muted', 'is-visible');
                heroStatItemTimers.delete(item);
            }, 2000);

            heroStatItemTimers.set(item, timerId);
        }
    }

    function collectStats() {
        return Array.from(list.querySelectorAll('[data-hero-stat-item]')).map(function (item) {
            const labelInput = item.querySelector('[data-hero-stat-label]');
            const valueInput = item.querySelector('[data-hero-stat-value]');
            const visibleInput = item.querySelector('[data-hero-stat-visible]');

            return {
                id: item.dataset.heroStatId || '',
                label: labelInput ? labelInput.value.trim() : '',
                value: valueInput ? valueInput.value.trim() : '',
                is_active: visibleInput && visibleInput.checked ? 1 : 0
            };
        });
    }

    function refreshPreview() {
        if (!previewContainer) {
            return;
        }

        const stats = collectStats().filter(function (stat) {
            return stat.is_active === 1 && stat.label !== '' && stat.value !== '';
        });

        previewContainer.innerHTML = '';

        if (!stats.length) {
            const empty = document.createElement('div');
            empty.className = 'col-12 text-light-50 small';
            empty.textContent = 'Add hero stats to highlight experience.';
            previewContainer.appendChild(empty);
            return;
        }

        stats.slice(0, 3).forEach(function (stat) {
            const col = document.createElement('div');
            col.className = 'col-4';

            const valueEl = document.createElement('div');
            valueEl.className = 'fw-semibold h4 mb-1';
            valueEl.textContent = stat.value;

            const labelEl = document.createElement('div');
            labelEl.className = 'text-uppercase small';
            labelEl.textContent = stat.label;

            col.appendChild(valueEl);
            col.appendChild(labelEl);
            previewContainer.appendChild(col);
        });
    }

    function updateCount() {
        const count = list.querySelectorAll('[data-hero-stat-item]').length;

        if (countBadge) {
            const label = count === 1 ? '1 total' : count + ' total';
            countBadge.textContent = label;
        }

        if (emptyState) {
            emptyState.classList.toggle('d-none', count > 0);
        }

        const atLimit = count >= limit;

        if (addButton) {
            addButton.disabled = atLimit;
        }

        if (addLabelInput) {
            addLabelInput.disabled = atLimit;
        }

        if (addValueInput) {
            addValueInput.disabled = atLimit;
        }

        if (addVisibleInput) {
            addVisibleInput.disabled = atLimit;
        }

        if (atLimit) {
            setFeedback('Limit reached: feature up to three stats on the hero.', 'warning', 'limit');
        } else if (feedback && feedback.dataset.feedbackKey === 'limit') {
            setFeedback('', '', '');
        }
    }

    function handleAdd(event) {
        event.preventDefault();

        if (!createUrl) {
            return;
        }

        const count = list.querySelectorAll('[data-hero-stat-item]').length;
        if (count >= limit) {
            setFeedback('Limit reached: remove a stat before adding another.', 'warning');
            return;
        }

        const label = addLabelInput ? addLabelInput.value.trim() : '';
        const value = addValueInput ? addValueInput.value.trim() : '';
        const isActive = addVisibleInput && addVisibleInput.checked ? 1 : 0;

        if (!label || !value) {
            setFeedback('Please provide both a label and value.', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('label', label);
        formData.append('value', value);
        if (isActive) {
            formData.append('is_active', '1');
        }

        if (addButton) {
            setButtonLoading(addButton, true);
        }

        fetch(createUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData,
            credentials: 'same-origin'
        })
            .then(parseJsonResponse)
            .then(function (payload) {
                if (!payload.ok || !payload.data || payload.data.success === false) {
                    const message = payload.data && payload.data.message ? payload.data.message : 'Failed to add stat.';
                    setFeedback(message, 'error');
                    throw new Error(message);
                }

                const stat = payload.data.stat || {};
                appendStat(stat);
                if (addForm) {
                    addForm.reset();
                }
                setFeedback('Hero stat added.', 'success', 'add-success');
            })
            .catch(function (error) {
                if (error.name === 'AbortError') {
                    return;
                }
                    setFeedback('Failed to add stat. Please try again.', 'error', 'add-error');
            })
            .finally(function () {
                if (addButton) {
                    setButtonLoading(addButton, false);
                }

                updateCount();
                refreshPreview();
            });
    }

    function parseJsonResponse(response) {
        const contentType = response.headers.get('content-type') || '';
        const isJson = contentType.includes('application/json');

        if (!isJson) {
            return Promise.reject(new Error('Unexpected response format'));
        }

        return response.json().then(function (data) {
            return { ok: response.ok, status: response.status, data: data };
        });
    }

    function appendStat(stat) {
        if (!stat) {
            return;
        }

        const element = createStatElement(stat);
        list.appendChild(element);
    }

    function saveStat(item, button) {
        if (!item) {
            return;
        }

        const updateUrl = item.dataset.updateUrl || '';
        if (!updateUrl) {
            setItemFeedback(item, 'Missing update endpoint.', 'error');
            return;
        }

        const labelInput = item.querySelector('[data-hero-stat-label]');
        const valueInput = item.querySelector('[data-hero-stat-value]');
        const visibleInput = item.querySelector('[data-hero-stat-visible]');

        const label = labelInput ? labelInput.value.trim() : '';
        const value = valueInput ? valueInput.value.trim() : '';
        const isActive = visibleInput && visibleInput.checked ? 1 : 0;

        if (!label || !value) {
            setItemFeedback(item, 'Both fields are required.', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('label', label);
        formData.append('value', value);
        if (isActive) {
            formData.append('is_active', '1');
        }

        setItemFeedback(item, 'Saving...', 'muted');
        setButtonLoading(button, true);

        fetch(updateUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData,
            credentials: 'same-origin'
        })
            .then(parseJsonResponse)
            .then(function (payload) {
                if (!payload.ok || !payload.data || payload.data.success === false) {
                    const message = payload.data && payload.data.message ? payload.data.message : 'Failed to save stat.';
                    setItemFeedback(item, message, 'error');
                    throw new Error(message);
                }

                const stat = payload.data.stat || {};
                item.dataset.updateUrl = stat.update_url || updateUrl;
                item.dataset.deleteUrl = stat.delete_url || item.dataset.deleteUrl || '';
                item.dataset.sortableId = String(stat.id || item.dataset.heroStatId || '');
                item.dataset.heroStatId = String(stat.id || item.dataset.heroStatId || '');
                item.dataset.sortOrder = String(stat.sort_order || item.dataset.sortOrder || '0');

                clearStatDirty(item);
                setItemFeedback(item, 'Saved', 'success');
                refreshPreview();
            })
            .catch(function (error) {
                if (error.name === 'AbortError') {
                    return;
                }
                setItemFeedback(item, 'Failed to save. Please try again.', 'error');
            })
            .finally(function () {
                setButtonLoading(button, false);
            });
    }

    function deleteStat(item, button) {
        if (!item) {
            return;
        }

        const deleteUrl = item.dataset.deleteUrl || '';
        if (!deleteUrl) {
            setItemFeedback(item, 'Missing delete endpoint.', 'error');
            return;
        }

        if (!window.confirm('Delete this hero stat?')) {
            return;
        }

        setButtonLoading(button, true);
        setItemFeedback(item, 'Removing...', 'muted');

        const formData = new FormData();

        fetch(deleteUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData,
            credentials: 'same-origin'
        })
            .then(parseJsonResponse)
            .then(function (payload) {
                if (!payload.ok || !payload.data || payload.data.success === false) {
                    const message = payload.data && payload.data.message ? payload.data.message : 'Failed to delete stat.';
                    setItemFeedback(item, message, 'error');
                    throw new Error(message);
                }

                item.remove();
                setFeedback('Hero stat removed.', 'success', 'delete-success');
            })
            .catch(function (error) {
                if (error.name === 'AbortError') {
                    return;
                }
                setItemFeedback(item, 'Failed to delete. Please try again.', 'error');
            })
            .finally(function () {
                setButtonLoading(button, false);
                updateCount();
                refreshPreview();
            });
    }

    function handleReorder() {
        if (!reorderUrl) {
            refreshPreview();
            return;
        }

        const ids = collectStats()
            .map(function (stat) {
                return stat.id;
            })
            .filter(Boolean);

        if (!ids.length) {
            refreshPreview();
            return;
        }

        if (state.reorderController) {
            state.reorderController.abort();
        }

        state.reorderController = new AbortController();

        const formData = new FormData();
        formData.append('order', ids.join(','));

        fetch(reorderUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData,
            credentials: 'same-origin',
            signal: state.reorderController.signal
        })
            .then(parseJsonResponse)
            .then(function (payload) {
                if (!payload.ok || !payload.data || payload.data.success === false) {
                    const message = payload.data && payload.data.message ? payload.data.message : 'Failed to update order.';
                    setFeedback(message, 'error');
                    return;
                }

                setFeedback('Hero stats order updated.', 'success');
            })
            .catch(function (error) {
                if (error.name === 'AbortError') {
                    return;
                }
                setFeedback('Failed to update order. Please try again.', 'error');
            })
            .finally(function () {
                refreshPreview();
            });
    }

    if (addForm) {
        addForm.addEventListener('submit', handleAdd);
    }

    list.addEventListener('sortable:reordered', function () {
        handleReorder();
    });

    initialData
        .sort(function (a, b) { return (a.sort_order || 0) - (b.sort_order || 0); })
        .forEach(appendStat);

    updateCount();
    refreshPreview();
}

function initializeFeaturedWorkManager() {
    const container = document.querySelector('[data-featured-work-manager]');
    if (!container) {
        return;
    }

    const form = container.querySelector('[data-featured-form]');
    const availableList = container.querySelector('[data-featured-available-list]');
    const featuredList = container.querySelector('[data-featured-list]');
    const previewGrid = container.querySelector('[data-featured-preview]');
    const feedback = container.querySelector('[data-featured-feedback]');
    const saveButton = container.querySelector('[data-featured-save]');
    const cancelButton = container.querySelector('[data-featured-cancel]');
    const orderInput = container.querySelector('[data-featured-order-input]');
    const countBadge = container.querySelector('[data-featured-count]');
    const rangeLabel = container.querySelector('[data-featured-range-label]');
    const availableCount = container.querySelector('[data-featured-available-count]');
    const emptyState = container.querySelector('[data-featured-empty]');
    const minCount = parseInt(container.dataset.featuredMin, 10) || 3;
    const maxCount = parseInt(container.dataset.featuredMax, 10) || 6;

    if (!form || !availableList || !featuredList || !previewGrid || !orderInput) {
        return;
    }

    const allProjects = parseFeaturedJson(container.dataset.featuredProjects);
    const initialFeatured = parseFeaturedJson(container.dataset.featuredInitial);
    const defaultImage = typeof container.dataset.featuredDefaultImage === 'string'
        ? container.dataset.featuredDefaultImage
        : '';

    const projectMap = new Map();

    allProjects.forEach(function (project) {
        const normalized = normalizeBaseProject(project);
        if (normalized) {
            projectMap.set(normalized.id, normalized);
        }
    });

    function resolveProjectImage(project) {
        if (!project) {
            return defaultImage;
        }

        const candidates = [
            project.main_image_url,
            project.main_image_path,
            project.image_url,
            project.thumbnail_url,
            project.thumbnail
        ];

        for (let index = 0; index < candidates.length; index += 1) {
            const value = candidates[index];
            if (typeof value === 'string' && value.trim() !== '') {
                return value.trim();
            }
        }

        return defaultImage;
    }

    function normalizeBaseProject(project) {
        if (!project) {
            return null;
        }
        const id = Number(project.id || project.project_id || 0);
        if (!id) {
            return null;
        }

        const imageUrl = resolveProjectImage(project);

        return {
            id: id,
            title: project.title || 'Untitled Project',
            short_description: project.short_description || '',
            main_image_url: imageUrl,
            status: project.status || 'draft',
            category: project.category || '',
            slug: project.slug || '',
            featured_sort_order: Number(project.featured_sort_order || project.sort_order || 0),
            sort_order: Number(project.sort_order || 0)
        };
    }

    function buildProject(project) {
        if (!project) {
            return null;
        }

        const id = Number(project.id || project.project_id || 0);
        if (!id) {
            return null;
        }

        const base = projectMap.get(id) || normalizeBaseProject(project);
        if (!base) {
            return null;
        }

    const merged = Object.assign({}, base, project);
        merged.id = id;
        merged.title = merged.title || base.title;
        merged.short_description = merged.short_description || base.short_description || '';
    merged.main_image_url = resolveProjectImage(merged);
        merged.status = merged.status || base.status || 'draft';
        merged.category = merged.category || base.category || '';
        merged.slug = merged.slug || base.slug || '';

        projectMap.set(id, Object.assign({}, merged));

        return {
            id: merged.id,
            title: merged.title,
            short_description: merged.short_description,
            main_image_url: merged.main_image_url,
            status: merged.status,
            category: merged.category,
            slug: merged.slug
        };
    }

    function cloneProject(project) {
        return {
            id: project.id,
            title: project.title,
            short_description: project.short_description,
            main_image_url: project.main_image_url,
            status: project.status,
            category: project.category,
            slug: project.slug
        };
    }

    const state = {
        featured: initialFeatured.map(buildProject).filter(Boolean),
        saved: initialFeatured.map(buildProject).filter(Boolean),
        timer: null
    };

        container.dataset.featuredInitial = JSON.stringify(state.saved);

    function parseFeaturedJson(raw) {
        if (!raw) {
            return [];
        }

        try {
            const parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            console.warn('Failed to parse featured projects dataset', error);
            return [];
        }
    }

    function getFeaturedIds() {
        return state.featured.map(function (project) {
            return project.id;
        });
    }

    function arraysEqual(a, b) {
        if (a.length !== b.length) {
            return false;
        }

        for (let index = 0; index < a.length; index += 1) {
            if (a[index] !== b[index]) {
                return false;
            }
        }
        return true;
    }

    function withinBounds(list) {
        return list.length >= minCount && list.length <= maxCount;
    }

    function getAvailableProjects() {
        const featuredIds = new Set(getFeaturedIds());
        return allProjects
            .map(buildProject)
            .filter(Boolean)
            .filter(function (project) {
                return !featuredIds.has(project.id);
            })
            .sort(function (a, b) {
                return a.title.localeCompare(b.title);
            });
    }

    function updateHiddenField() {
        const order = getFeaturedIds().join(',');
        orderInput.value = order;
    }

    function updateDefaultHiddenField() {
        const order = getFeaturedIds().join(',');
        orderInput.defaultValue = order;
        orderInput.value = order;
    }

    function updateCounts() {
        const selectedCount = state.featured.length;
        if (countBadge) {
            countBadge.textContent = selectedCount + ' selected';
        }
        if (rangeLabel) {
            rangeLabel.textContent = selectedCount + '/' + maxCount + ' selected';
        }
        if (availableCount) {
            availableCount.textContent = getAvailableProjects().length;
        }
    }

    function renderAvailableList() {
        const template = document.getElementById('featuredAvailableTemplate');
        if (!template || !template.content) {
            return;
        }

        const availableProjects = getAvailableProjects();
        availableList.innerHTML = '';

        if (!availableProjects.length) {
            const emptyItem = document.createElement('li');
            emptyItem.className = 'list-group-item text-muted text-center py-4';
            emptyItem.textContent = 'All projects are currently featured.';
            availableList.appendChild(emptyItem);
            return;
        }

        const canAddMore = state.featured.length < maxCount;

        availableProjects.forEach(function (project) {
            const fragment = template.content.cloneNode(true);
            const item = fragment.querySelector('[data-project-id]');
            const image = fragment.querySelector('img');
            const title = fragment.querySelector('[data-project-title]');
            const description = fragment.querySelector('[data-project-description]');
            const statusBadge = fragment.querySelector('[data-project-status]');
            const addButton = fragment.querySelector('[data-featured-add]');

            item.dataset.projectId = String(project.id);

            if (image) {
                image.src = project.main_image_url || '';
                image.alt = project.title + ' thumbnail';
            }
            if (title) {
                title.textContent = project.title;
            }
            if (description) {
                description.textContent = project.short_description || 'No short description available.';
            }
            if (statusBadge) {
                if (project.status && project.status.toLowerCase() !== 'published') {
                    statusBadge.classList.remove('d-none');
                } else {
                    statusBadge.classList.add('d-none');
                }
            }
            if (addButton) {
                addButton.disabled = !canAddMore;
            }

            availableList.appendChild(fragment);
        });
    }

    function renderFeaturedList() {
        const template = document.getElementById('featuredSelectedTemplate');
        if (!template || !template.content) {
            return;
        }

        featuredList.innerHTML = '';

        state.featured.forEach(function (project) {
            const fragment = template.content.cloneNode(true);
            const item = fragment.querySelector('[data-featured-item]');
            const image = fragment.querySelector('img');
            const title = fragment.querySelector('[data-project-title]');
            const description = fragment.querySelector('[data-project-description]');

            if (item) {
                item.dataset.featuredId = String(project.id);
            }

            if (image) {
                image.src = project.main_image_url || '';
                image.alt = project.title + ' thumbnail';
            }

            if (title) {
                title.textContent = project.title;
            }

            if (description) {
                description.textContent = project.short_description || 'No short description available.';
            }

            featuredList.appendChild(fragment);
        });

        if (emptyState) {
            if (state.featured.length === 0) {
                emptyState.classList.remove('d-none');
            } else {
                emptyState.classList.add('d-none');
            }
        }
    }

    function renderPreview() {
        const template = document.getElementById('featuredPreviewTemplate');
        const placeholderTemplate = document.getElementById('featuredPlaceholderTemplate');
        if (!template || !template.content || !placeholderTemplate || !placeholderTemplate.content) {
            return;
        }

        previewGrid.innerHTML = '';

        state.featured.forEach(function (project) {
            const fragment = template.content.cloneNode(true);
            const image = fragment.querySelector('img');
            const title = fragment.querySelector('[data-project-title]');
            const description = fragment.querySelector('[data-project-description]');

            if (image) {
                image.src = project.main_image_url || '';
                image.alt = project.title + ' preview';
            }
            if (title) {
                title.textContent = project.title;
            }
            if (description) {
                description.textContent = project.short_description || 'Featured project summary.';
            }

            previewGrid.appendChild(fragment);
        });

        if (state.featured.length < minCount) {
            const placeholdersNeeded = minCount - state.featured.length;
            for (let index = 0; index < placeholdersNeeded; index += 1) {
                previewGrid.appendChild(placeholderTemplate.content.cloneNode(true));
            }
        }
    }

    function showFeedback(message, tone, key) {
        if (!feedback) {
            return;
        }

        if (state.timer) {
            window.clearTimeout(state.timer);
            state.timer = null;
        }

        if (!message) {
            if (typeof key === 'string' && feedback.dataset.feedbackKey && feedback.dataset.feedbackKey !== key) {
                return;
            }
            feedback.textContent = '';
            feedback.classList.add('d-none');
            feedback.classList.remove('text-danger', 'text-success', 'text-warning', 'text-muted');
            if (feedback.dataset.feedbackKey) {
                delete feedback.dataset.feedbackKey;
            }
            return;
        }

        if (typeof key === 'string') {
            feedback.dataset.feedbackKey = key;
        } else if (feedback.dataset.feedbackKey) {
            delete feedback.dataset.feedbackKey;
        }

        feedback.textContent = message;
        feedback.classList.remove('d-none', 'text-danger', 'text-success', 'text-warning', 'text-muted');

        switch (tone) {
            case 'error':
                feedback.classList.add('text-danger');
                break;
            case 'success':
                feedback.classList.add('text-success');
                break;
            case 'warning':
                feedback.classList.add('text-warning');
                break;
            default:
                feedback.classList.add('text-muted');
        }

        if (tone === 'success') {
            const appliedKey = feedback.dataset.feedbackKey || null;
            state.timer = window.setTimeout(function () {
                if (appliedKey && feedback.dataset.feedbackKey && feedback.dataset.feedbackKey !== appliedKey) {
                    return;
                }
                feedback.textContent = '';
                feedback.classList.add('d-none');
                feedback.classList.remove('text-danger', 'text-success', 'text-warning', 'text-muted');
                if (!appliedKey || feedback.dataset.feedbackKey === appliedKey) {
                    delete feedback.dataset.feedbackKey;
                }
                state.timer = null;
            }, 2500);
        }
    }

    function updateControls() {
        const selectedIds = getFeaturedIds();
        const isDirty = !arraysEqual(selectedIds, state.saved.map(function (project) {
            return project.id;
        }));
        const validSelection = withinBounds(state.featured);

        if (saveButton) {
            saveButton.disabled = !isDirty || !validSelection;
        }

        if (!validSelection) {
            const hasForeignFeedback = feedback && feedback.dataset.feedbackKey && feedback.dataset.feedbackKey !== 'bounds';

            if (!hasForeignFeedback && state.featured.length < minCount) {
                const needed = minCount - state.featured.length;
                showFeedback('Add ' + needed + ' more project' + (needed === 1 ? '' : 's') + ' to meet the minimum.', 'warning', 'bounds');
            } else if (!hasForeignFeedback && state.featured.length > maxCount) {
                const over = state.featured.length - maxCount;
                showFeedback('Remove ' + over + ' project' + (over === 1 ? '' : 's') + ' to meet the limit.', 'warning', 'bounds');
            }
        } else if (feedback && feedback.dataset.feedbackKey === 'bounds') {
            showFeedback('', '', 'bounds');
        }

        updateCounts();
        updateHiddenField();
    }

    function renderAll() {
        renderAvailableList();
        renderFeaturedList();
        renderPreview();
        updateControls();
    }

    renderAll();
    updateDefaultHiddenField();

    function handleAddClick(event) {
        const button = event.target.closest('[data-featured-add]');
        if (!button) {
            return;
        }

        if (state.featured.length >= maxCount) {
            showFeedback('You can feature up to ' + maxCount + ' projects. Remove one to add another.', 'warning');
            return;
        }

        const item = button.closest('[data-project-id]');
        if (!item) {
            return;
        }

        const projectId = Number(item.dataset.projectId || '0');
        if (!projectId || state.featured.some(function (project) { return project.id === projectId; })) {
            return;
        }

        const project = buildProject({ id: projectId });
        if (!project) {
            return;
        }

        state.featured.push(cloneProject(project));
        renderAll();
        showFeedback('Added "' + project.title + '" to the featured list.', 'muted');
    }

    function handleRemoveClick(event) {
        const button = event.target.closest('[data-featured-remove]');
        if (!button) {
            return;
        }

        const item = button.closest('[data-featured-item]');
        if (!item) {
            return;
        }

        const projectId = Number(item.dataset.featuredId || '0');
        if (!projectId) {
            return;
        }

        state.featured = state.featured.filter(function (project) {
            return project.id !== projectId;
        });

        renderAll();

        if (state.featured.length < minCount) {
            const needed = minCount - state.featured.length;
            showFeedback('Add ' + needed + ' more project' + (needed === 1 ? '' : 's') + ' to meet the minimum.', 'warning');
        } else {
            showFeedback('Project removed from the featured list.', 'muted');
        }
    }

    function handleReorder() {
        const ids = Array.from(featuredList.querySelectorAll('[data-featured-id]')).map(function (element) {
            return Number(element.dataset.featuredId || '0');
        }).filter(function (id) {
            return id > 0;
        });

        if (!ids.length || ids.length !== state.featured.length) {
            return;
        }

        const lookup = new Map();
        state.featured.forEach(function (project) {
            lookup.set(project.id, project);
        });

        state.featured = ids.map(function (id) {
            const project = lookup.get(id) || buildProject({ id: id });
            return project ? cloneProject(project) : null;
        }).filter(Boolean);

        renderAll();
    }

    function parseFeaturedResponse(response) {
        const contentType = response.headers.get('content-type') || '';
        const isJson = contentType.includes('application/json');

        if (!isJson) {
            return Promise.reject(new Error('Unexpected response format'));
        }

        return response.json().then(function (data) {
            return { ok: response.ok, status: response.status, data: data };
        });
    }

    function handleSave(event) {
        event.preventDefault();

        if (!withinBounds(state.featured)) {
            showFeedback('Select between ' + minCount + ' and ' + maxCount + ' projects before saving.', 'warning');
            return;
        }

        const isDirty = !arraysEqual(getFeaturedIds(), state.saved.map(function (project) { return project.id; }));
        if (!isDirty) {
            showFeedback('No changes to save.', 'muted');
            return;
        }

        const formData = new FormData(form);
        formData.set('featured', getFeaturedIds().join(','));

        if (saveButton) {
            setButtonLoading(saveButton, true);
        }

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData,
            credentials: 'same-origin'
        })
            .then(parseFeaturedResponse)
            .then(function (payload) {
                if (!payload.ok || !payload.data || payload.data.success === false) {
                    const message = payload.data && payload.data.message ? payload.data.message : 'Failed to update featured projects.';
                    showFeedback(message, 'error');
                    throw new Error(message);
                }

                const projects = Array.isArray(payload.data.projects) ? payload.data.projects : [];
                const normalized = projects.map(buildProject).filter(Boolean);

                state.featured = normalized.map(cloneProject);
                state.saved = normalized.map(cloneProject);

                container.dataset.featuredInitial = JSON.stringify(state.saved);
                updateDefaultHiddenField();
                renderAll();
                showFeedback('Featured projects updated successfully.', 'success');
            })
            .catch(function (error) {
                if (error.name === 'AbortError') {
                    return;
                }
                showFeedback('Failed to update featured projects. Please try again.', 'error');
            })
            .finally(function () {
                if (saveButton) {
                    setButtonLoading(saveButton, false);
                }
            });
    }

    function handleCancel(event) {
        event.preventDefault();

        state.featured = state.saved.map(cloneProject);
        renderAll();
        updateDefaultHiddenField();
        showFeedback('Changes discarded.', 'muted');
    }

    availableList.addEventListener('click', handleAddClick);
    featuredList.addEventListener('click', handleRemoveClick);
    featuredList.addEventListener('sortable:reordered', handleReorder);
    form.addEventListener('submit', handleSave);

    if (cancelButton) {
        cancelButton.addEventListener('click', handleCancel);
    }
}

function initializeServicesPreviewManager() {
    const container = document.querySelector('[data-services-preview-manager]');
    if (!container) {
        return;
    }

    const form = container.querySelector('[data-services-form]');
    const availableList = container.querySelector('[data-services-available-list]');
    const selectedList = container.querySelector('[data-services-selected-list]');
    const previewGrid = container.querySelector('[data-services-preview]');
    const feedback = container.querySelector('[data-services-feedback]');
    const saveButton = container.querySelector('[data-services-save]');
    const cancelButton = container.querySelector('[data-services-cancel]');
    const payloadInput = container.querySelector('[data-services-payload]');
    const countBadge = container.querySelector('[data-services-count]');
    const rangeLabel = container.querySelector('[data-services-range-label]');
    const availableCount = container.querySelector('[data-services-available-count]');
    const emptyState = container.querySelector('[data-services-empty]');
    const minRequired = parseInt(container.dataset.servicesMin, 10) || 3;
    const maxAllowed = parseInt(container.dataset.servicesMax, 10) || 6;

    if (!form || !availableList || !selectedList || !previewGrid || !payloadInput) {
        return;
    }

    const serviceMap = new Map();

    function parseServices(raw) {
        if (!raw) {
            return [];
        }

        try {
            const parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            console.warn('Failed to parse services dataset', error);
            return [];
        }
    }

    function normalizeServiceRecord(service) {
        if (!service) {
            return null;
        }

        const id = Number(service.id || service.service_id || 0);
        if (!id) {
            return null;
        }

        const icon = (service.icon || '').trim();
        const status = service.status || 'draft';
        const summary = typeof service.summary === 'string' ? service.summary : '';
        const title = service.title || 'Untitled Service';
        const sortSource = service.sort_order || service.homepage_sort_order || 0;
        const rawVisible = typeof service.visible !== 'undefined' ? service.visible : service.homepage_is_visible;

        let visible;
        if (typeof rawVisible === 'boolean') {
            visible = rawVisible;
        } else if (typeof rawVisible === 'number') {
            visible = rawVisible === 1;
        } else if (rawVisible === '1') {
            visible = true;
        } else if (rawVisible === '0') {
            visible = false;
        } else {
            visible = true;
        }

        return {
            id: id,
            title: title,
            summary: summary,
            icon: icon,
            status: status,
            visible: visible,
            sort_order: Number(sortSource || 0)
        };
    }

    function buildService(service) {
        if (!service) {
            return null;
        }

        const id = Number(service.id || service.service_id || 0);
        if (!id) {
            return null;
        }

        const base = serviceMap.get(id);
        if (!base) {
            const normalized = normalizeServiceRecord(service);
            if (!normalized) {
                return null;
            }
            serviceMap.set(id, Object.assign({}, normalized));
            return normalized;
        }

        const merged = Object.assign({}, base);

        if (typeof service.title === 'string' && service.title.trim() !== '') {
            merged.title = service.title;
        }

        if (typeof service.summary === 'string') {
            merged.summary = service.summary;
        }

        if (typeof service.icon === 'string' && service.icon.trim() !== '') {
            merged.icon = service.icon.trim();
        }

        if (typeof service.status === 'string' && service.status.trim() !== '') {
            merged.status = service.status;
        }

        if (typeof service.sort_order !== 'undefined' || typeof service.homepage_sort_order !== 'undefined') {
            const sortCandidate = Number(service.sort_order || service.homepage_sort_order || 0);
            if (!Number.isNaN(sortCandidate)) {
                merged.sort_order = sortCandidate;
            }
        }

        if (typeof service.visible !== 'undefined' || typeof service.homepage_is_visible !== 'undefined') {
            const raw = typeof service.visible !== 'undefined' ? service.visible : service.homepage_is_visible;
            if (typeof raw === 'boolean') {
                merged.visible = raw;
            } else if (typeof raw === 'number') {
                merged.visible = raw === 1;
            } else if (raw === '1') {
                merged.visible = true;
            } else if (raw === '0') {
                merged.visible = false;
            }
        }

        serviceMap.set(id, Object.assign({}, merged));

        return merged;
    }

    const allServicesRaw = parseServices(container.dataset.servicesAll);
    allServicesRaw.forEach(function (service) {
        const normalized = normalizeServiceRecord(service);
        if (normalized) {
            serviceMap.set(normalized.id, normalized);
        }
    });

    const initialSelectedRaw = parseServices(container.dataset.servicesInitial);
    const initialSelected = initialSelectedRaw.map(buildService).filter(Boolean);

    const state = {
        selected: initialSelected.map(cloneService),
        saved: initialSelected.map(cloneService),
        timer: null
    };

    container.dataset.servicesInitial = JSON.stringify(state.saved);

    function cloneService(service) {
        return {
            id: service.id,
            title: service.title,
            summary: service.summary,
            icon: service.icon,
            status: service.status,
            visible: !!service.visible,
            sort_order: service.sort_order
        };
    }

    function getSelectedIds() {
        return state.selected.map(function (service) {
            return service.id;
        });
    }

    function withinBounds() {
        const count = state.selected.length;
        return count >= minRequired && count <= maxAllowed;
    }

    function getVisibleCount() {
        return state.selected.filter(function (service) {
            return service.visible;
        }).length;
    }

    function getAvailableServices() {
        const selectedIds = new Set(getSelectedIds());
        return Array.from(serviceMap.values())
            .filter(function (service) {
                return !selectedIds.has(service.id);
            })
            .sort(function (a, b) {
                return a.title.localeCompare(b.title);
            });
    }

    function extractServiceFromElement(element) {
        if (!element) {
            return null;
        }

        const id = Number(element.dataset.serviceId || element.dataset.sortableId || '0');
        if (!id) {
            return null;
        }

        const titleElement = element.querySelector('[data-service-title]');
        const summaryElement = element.querySelector('[data-service-summary]');
        const iconElement = element.querySelector('[data-service-icon]');
        const statusBadge = element.querySelector('[data-service-status]');

        let iconClass = '';
        if (iconElement) {
            const classes = Array.from(iconElement.classList).filter(function (className) {
                return className !== 'bi';
            });
            iconClass = classes.length ? classes.join(' ') : '';
        }

        const title = titleElement ? titleElement.textContent.trim() : 'Untitled Service';
        const summary = summaryElement ? summaryElement.textContent.trim() : '';
        const status = statusBadge && !statusBadge.classList.contains('d-none') ? 'draft' : 'published';

        return {
            id: id,
            title: title,
            summary: summary,
            icon: iconClass,
            status: status,
            visible: true,
            sort_order: state.selected.length + 1
        };
    }

    function updatePayloadInput() {
        const serialized = JSON.stringify(state.selected.map(function (service) {
            return {
                id: service.id,
                visible: service.visible ? 1 : 0
            };
        }));
        payloadInput.value = serialized;
    }

    function renderAvailableList() {
        const template = document.getElementById('servicesAvailableTemplate');
        if (!template || !template.content) {
            return;
        }

        const availableServices = getAvailableServices();
        availableList.innerHTML = '';

        if (!availableServices.length) {
            const emptyItem = document.createElement('li');
            emptyItem.className = 'list-group-item text-muted text-center py-4';
            emptyItem.setAttribute('data-services-available-empty', '');
            emptyItem.textContent = 'All services are currently selected.';
            availableList.appendChild(emptyItem);
            if (availableCount) {
                availableCount.textContent = '0';
            }
            return;
        }

        const canAddMore = state.selected.length < maxAllowed;

        availableServices.forEach(function (service) {
            const fragment = template.content.cloneNode(true);
            const item = fragment.querySelector('[data-service-id]');
            const icon = fragment.querySelector('[data-service-icon]');
            const title = fragment.querySelector('[data-service-title]');
            const summary = fragment.querySelector('[data-service-summary]');
            const statusBadge = fragment.querySelector('[data-service-status]');
            const addButton = fragment.querySelector('[data-service-add]');

            if (item) {
                item.dataset.serviceId = String(service.id);
            }

            if (icon) {
                const iconClass = service.icon && service.icon.trim() !== '' ? service.icon.trim() : 'bi-briefcase';
                icon.className = 'bi ' + iconClass;
            }

            if (title) {
                title.textContent = service.title;
            }

            if (summary) {
                summary.textContent = service.summary || 'No short description available.';
            }

            if (statusBadge) {
                if (service.status && service.status.toLowerCase() !== 'published') {
                    statusBadge.classList.remove('d-none');
                } else {
                    statusBadge.classList.add('d-none');
                }
            }

            if (addButton) {
                addButton.disabled = !canAddMore;
            }

            availableList.appendChild(fragment);
        });

        if (availableCount) {
            availableCount.textContent = String(availableServices.length);
        }
    }

    function renderSelectedList() {
        const template = document.getElementById('servicesSelectedTemplate');
        if (!template || !template.content) {
            return;
        }

        selectedList.innerHTML = '';

        state.selected.forEach(function (service) {
            const fragment = template.content.cloneNode(true);
            const item = fragment.querySelector('[data-service-item]');
            const icon = fragment.querySelector('[data-service-icon]');
            const title = fragment.querySelector('[data-service-title]');
            const summary = fragment.querySelector('[data-service-summary]');
            const toggle = fragment.querySelector('[data-service-visible-toggle]');

            if (item) {
                item.dataset.serviceId = String(service.id);
                item.dataset.sortableId = String(service.id);
            }

            if (icon) {
                const iconClass = service.icon && service.icon.trim() !== '' ? service.icon.trim() : 'bi-briefcase';
                icon.className = 'bi ' + iconClass;
            }

            if (title) {
                title.textContent = service.title;
            }

            if (summary) {
                summary.textContent = service.summary || '';
            }

            if (toggle) {
                toggle.checked = !!service.visible;
            }

            selectedList.appendChild(fragment);
        });

        if (emptyState) {
            emptyState.classList.toggle('d-none', state.selected.length > 0);
        }
    }

    function renderPreview() {
        const template = document.getElementById('servicesPreviewTemplate');
        const placeholderTemplate = document.getElementById('servicesPlaceholderTemplate');
        if (!template || !template.content || !placeholderTemplate || !placeholderTemplate.content) {
            return;
        }

        previewGrid.innerHTML = '';

        const visibleServices = state.selected.filter(function (service) {
            return service.visible;
        });

        visibleServices.forEach(function (service) {
            const fragment = template.content.cloneNode(true);
            const icon = fragment.querySelector('[data-service-icon]');
            const title = fragment.querySelector('[data-service-title]');
            const summary = fragment.querySelector('[data-service-summary]');

            if (icon) {
                const iconClass = service.icon && service.icon.trim() !== '' ? service.icon.trim() : 'bi-briefcase';
                icon.className = 'bi ' + iconClass;
            }

            if (title) {
                title.textContent = service.title;
            }

            if (summary) {
                summary.textContent = service.summary || '';
            }

            previewGrid.appendChild(fragment);
        });

        if (visibleServices.length < minRequired) {
            const needed = minRequired - visibleServices.length;
            for (let index = 0; index < needed; index += 1) {
                previewGrid.appendChild(placeholderTemplate.content.cloneNode(true));
            }
        }
    }

    function arraysEqual(a, b) {
        if (a.length !== b.length) {
            return false;
        }

        for (let index = 0; index < a.length; index += 1) {
            if (a[index] !== b[index]) {
                return false;
            }
        }

        return true;
    }

    function showFeedback(message, tone, key) {
        if (!feedback) {
            return;
        }

        if (state.timer) {
            window.clearTimeout(state.timer);
            state.timer = null;
        }

        if (!message) {
            if (typeof key === 'string' && feedback.dataset.feedbackKey && feedback.dataset.feedbackKey !== key) {
                return;
            }
            feedback.textContent = '';
            feedback.classList.add('d-none');
            feedback.classList.remove('text-danger', 'text-success', 'text-warning', 'text-muted');
            if (!key || feedback.dataset.feedbackKey === key) {
                delete feedback.dataset.feedbackKey;
            }
            return;
        }

        if (typeof key === 'string') {
            feedback.dataset.feedbackKey = key;
        } else if (feedback.dataset.feedbackKey) {
            delete feedback.dataset.feedbackKey;
        }

        feedback.textContent = message;
        feedback.classList.remove('d-none', 'text-danger', 'text-success', 'text-warning', 'text-muted');

        switch (tone) {
            case 'error':
                feedback.classList.add('text-danger');
                break;
            case 'success':
                feedback.classList.add('text-success');
                break;
            case 'warning':
                feedback.classList.add('text-warning');
                break;
            default:
                feedback.classList.add('text-muted');
        }

        if (tone === 'success') {
            const appliedKey = feedback.dataset.feedbackKey || null;
            state.timer = window.setTimeout(function () {
                if (appliedKey && feedback.dataset.feedbackKey && feedback.dataset.feedbackKey !== appliedKey) {
                    return;
                }
                feedback.textContent = '';
                feedback.classList.add('d-none');
                feedback.classList.remove('text-danger', 'text-success', 'text-warning', 'text-muted');
                if (!appliedKey || feedback.dataset.feedbackKey === appliedKey) {
                    delete feedback.dataset.feedbackKey;
                }
                state.timer = null;
            }, 2500);
        }
    }

    function updateCounts() {
        const selectedCount = state.selected.length;

        if (countBadge) {
            countBadge.textContent = selectedCount + ' selected';
        }

        if (rangeLabel) {
            rangeLabel.textContent = selectedCount + '/' + maxAllowed + ' selected';
        }

        if (availableCount) {
            availableCount.textContent = String(getAvailableServices().length);
        }
    }

    function updateControls() {
        const selectedIds = getSelectedIds();
        const savedIds = state.saved.map(function (service) {
            return service.id;
        });

        const savedMap = new Map();
        state.saved.forEach(function (service) {
            savedMap.set(service.id, service);
        });

        const hasVisibilityChanges = state.selected.some(function (service) {
            const savedService = savedMap.get(service.id);
            if (!savedService) {
                return true;
            }
            return savedService.visible !== service.visible;
        });

        const isDirty = !arraysEqual(selectedIds, savedIds) || hasVisibilityChanges;
        const inBounds = withinBounds();
        const visibleCount = getVisibleCount();

        if (saveButton) {
            saveButton.disabled = !isDirty || !inBounds || visibleCount < minRequired;
        }

        if (!inBounds) {
            const hasForeignFeedback = feedback && feedback.dataset.feedbackKey && feedback.dataset.feedbackKey !== 'bounds';
            if (!hasForeignFeedback && state.selected.length < minRequired) {
                const needed = minRequired - state.selected.length;
                showFeedback('Add ' + needed + ' more service' + (needed === 1 ? '' : 's') + ' to meet the minimum.', 'warning', 'bounds');
            } else if (!hasForeignFeedback && state.selected.length > maxAllowed) {
                const excess = state.selected.length - maxAllowed;
                showFeedback('Remove ' + excess + ' service' + (excess === 1 ? '' : 's') + ' to meet the limit.', 'warning', 'bounds');
            }
        } else if (feedback && feedback.dataset.feedbackKey === 'bounds') {
            showFeedback('', '', 'bounds');
        }

        if (visibleCount < minRequired) {
            showFeedback('Keep at least ' + minRequired + ' services visible for the homepage.', 'warning', 'visibility');
        } else if (feedback && feedback.dataset.feedbackKey === 'visibility') {
            showFeedback('', '', 'visibility');
        }

        updatePayloadInput();
    }

    function renderAll() {
        renderAvailableList();
        renderSelectedList();
        renderPreview();
        updateCounts();
        updateControls();
    }

    renderAll();
    payloadInput.defaultValue = payloadInput.value;

    function handleAddClick(event) {
        const button = event.target.closest('[data-service-add]');
        if (!button) {
            return;
        }

        if (state.selected.length >= maxAllowed) {
            showFeedback('You can feature up to ' + maxAllowed + ' services. Remove one to add another.', 'warning');
            return;
        }

        const item = button.closest('[data-service-id]');
        if (!item) {
            return;
        }

        const serviceId = Number(item.dataset.serviceId || '0');
        if (!serviceId || state.selected.some(function (service) { return service.id === serviceId; })) {
            return;
        }

        let service = buildService({ id: serviceId });
        if (!service) {
            service = extractServiceFromElement(item);
            if (service) {
                serviceMap.set(service.id, cloneService(service));
            }
        }
        if (!service) {
            return;
        }

        if (!serviceMap.has(service.id)) {
            serviceMap.set(service.id, cloneService(service));
        }

        const next = cloneService(service);
        next.visible = true;
        next.sort_order = state.selected.length + 1;

        state.selected.push(next);
        renderAll();
        showFeedback('Added "' + next.title + '" to the homepage selection.', 'muted');
    }

    function handleRemoveClick(event) {
        const button = event.target.closest('[data-service-remove]');
        if (!button) {
            return;
        }

        const item = button.closest('[data-service-item]');
        if (!item) {
            return;
        }

        const serviceId = Number(item.dataset.serviceId || '0');
        if (!serviceId) {
            return;
        }

        state.selected = state.selected.filter(function (service) {
            return service.id !== serviceId;
        });

        renderAll();

        if (state.selected.length < minRequired) {
            const needed = minRequired - state.selected.length;
            showFeedback('Add ' + needed + ' more service' + (needed === 1 ? '' : 's') + ' to meet the minimum.', 'warning', 'bounds');
        } else if (getVisibleCount() < minRequired) {
            showFeedback('At least ' + minRequired + ' services must remain visible.', 'warning', 'visibility');
        } else {
            showFeedback('Service removed from the homepage selection.', 'muted');
        }
    }

    function handleVisibleToggle(event) {
        const toggle = event.target.closest('[data-service-visible-toggle]');
        if (!toggle) {
            return;
        }

        const item = toggle.closest('[data-service-item]');
        if (!item) {
            return;
        }

        const serviceId = Number(item.dataset.serviceId || '0');
        if (!serviceId) {
            return;
        }

        const service = state.selected.find(function (entry) {
            return entry.id === serviceId;
        });

        if (!service) {
            return;
        }

        service.visible = toggle.checked;

        if (getVisibleCount() < minRequired) {
            showFeedback('At least ' + minRequired + ' services must remain visible.', 'warning', 'visibility');
        } else if (feedback && feedback.dataset.feedbackKey === 'visibility') {
            showFeedback('', '', 'visibility');
        }

        renderPreview();
        updateControls();
    }

    function handleReorder() {
        const ids = Array.from(selectedList.querySelectorAll('[data-service-item]')).map(function (element) {
            return Number(element.dataset.serviceId || '0');
        }).filter(function (id) {
            return id > 0;
        });

        if (!ids.length || ids.length !== state.selected.length) {
            return;
        }

        const lookup = new Map();
        state.selected.forEach(function (service) {
            lookup.set(service.id, service);
        });

        state.selected = ids.map(function (id) {
            const service = lookup.get(id) || buildService({ id: id });
            return service ? cloneService(service) : null;
        }).filter(Boolean);

        state.selected.forEach(function (service, index) {
            service.sort_order = index + 1;
        });

        renderAll();
    }

    function parseServicesResponse(response) {
        const contentType = response.headers.get('content-type') || '';
        const isJson = contentType.includes('application/json');

        if (!isJson) {
            return Promise.reject(new Error('Unexpected response format'));
        }

        return response.json().then(function (data) {
            return { ok: response.ok, status: response.status, data: data };
        });
    }

    function handleSave(event) {
        event.preventDefault();

        if (!withinBounds()) {
            showFeedback('Select between ' + minRequired + ' and ' + maxAllowed + ' services before saving.', 'warning');
            return;
        }

        if (getVisibleCount() < minRequired) {
            showFeedback('Keep at least ' + minRequired + ' services visible for the homepage.', 'warning');
            return;
        }

        const selectedIds = getSelectedIds();
        const savedIds = state.saved.map(function (service) {
            return service.id;
        });

        const savedMap = new Map();
        state.saved.forEach(function (service) {
            savedMap.set(service.id, service);
        });

        const hasVisibilityChanges = state.selected.some(function (service) {
            const savedService = savedMap.get(service.id);
            if (!savedService) {
                return true;
            }
            return savedService.visible !== service.visible;
        });

        if (arraysEqual(selectedIds, savedIds) && !hasVisibilityChanges) {
            showFeedback('No changes to save.', 'muted');
            return;
        }

        updatePayloadInput();

        const formData = new FormData(form);
        formData.set('services', payloadInput.value);

        if (saveButton) {
            setButtonLoading(saveButton, true);
        }

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData,
            credentials: 'same-origin'
        })
            .then(parseServicesResponse)
            .then(function (payload) {
                if (!payload.ok || !payload.data || payload.data.success === false) {
                    const message = payload.data && payload.data.message ? payload.data.message : 'Failed to update homepage services.';
                    showFeedback(message, 'error');
                    throw new Error(message);
                }

                const returnedServices = Array.isArray(payload.data.services) ? payload.data.services : [];
                const normalized = returnedServices.map(buildService).filter(Boolean);

                state.selected = normalized.map(cloneService);
                state.saved = normalized.map(cloneService);

                container.dataset.servicesInitial = JSON.stringify(state.saved);
                renderAll();
                updatePayloadInput();
                payloadInput.defaultValue = payloadInput.value;

                const successMessage = payload.data.message || 'Homepage services updated successfully.';
                showFeedback(successMessage, 'success');
            })
            .catch(function (error) {
                if (error.name === 'AbortError') {
                    return;
                }
                showFeedback('Failed to update homepage services. Please try again.', 'error');
            })
            .finally(function () {
                if (saveButton) {
                    setButtonLoading(saveButton, false);
                }
            });
    }

    function handleCancel(event) {
        event.preventDefault();

        state.selected = state.saved.map(cloneService);
        renderAll();
        updatePayloadInput();
        payloadInput.value = payloadInput.defaultValue;
        showFeedback('Changes discarded.', 'muted');
    }

    availableList.addEventListener('click', handleAddClick);
    selectedList.addEventListener('click', handleRemoveClick);
    selectedList.addEventListener('change', handleVisibleToggle);
    selectedList.addEventListener('sortable:reordered', handleReorder);
    form.addEventListener('submit', handleSave);

    if (cancelButton) {
        cancelButton.addEventListener('click', handleCancel);
    }
}

function initializeSkillsPreviewManager() {
    const container = document.querySelector('[data-skills-manager]');
    if (!container) {
        return;
    }

    const form = container.querySelector('[data-skills-form]');
    const payloadInput = container.querySelector('[data-skills-payload]');
    const categoryList = container.querySelector('[data-category-list]');
    const categoryEmpty = container.querySelector('[data-category-empty]');
    const previewGrid = container.querySelector('[data-skills-preview]');
    const previewEmpty = container.querySelector('[data-preview-empty]');
    const feedback = container.querySelector('[data-skills-feedback]');
    const addCategoryButtons = container.querySelectorAll('[data-add-category]');
    const cancelButtons = container.querySelectorAll('[data-skills-cancel]');
    const saveButtons = container.querySelectorAll('[data-skills-save]');
    const categoryTemplate = document.getElementById('skillCategoryTemplate');
    const skillTemplate = document.getElementById('skillItemTemplate');
    const previewCategoryTemplate = document.getElementById('skillPreviewCategoryTemplate');
    const previewSkillTemplate = document.getElementById('skillPreviewSkillTemplate');

    if (!form || !payloadInput || !categoryList || !categoryTemplate || !skillTemplate || !previewCategoryTemplate || !previewSkillTemplate) {
        return;
    }

    const categoryModalElement = document.getElementById('categoryModal');
    const skillModalElement = document.getElementById('skillModal');
    const categoryModal = categoryModalElement && typeof bootstrap !== 'undefined' ? new bootstrap.Modal(categoryModalElement) : null;
    const skillModal = skillModalElement && typeof bootstrap !== 'undefined' ? new bootstrap.Modal(skillModalElement) : null;
    const categoryForm = categoryModalElement ? categoryModalElement.querySelector('[data-category-form]') : null;
    const skillForm = skillModalElement ? skillModalElement.querySelector('[data-skill-form]') : null;
    const categoryModalTitle = categoryModalElement ? categoryModalElement.querySelector('[data-category-modal-title]') : null;
    const skillModalTitle = skillModalElement ? skillModalElement.querySelector('[data-skill-modal-title]') : null;
    const categoryModalError = categoryModalElement ? categoryModalElement.querySelector('[data-category-modal-error]') : null;
    const skillModalError = skillModalElement ? skillModalElement.querySelector('[data-skill-modal-error]') : null;
    const categoryModalCancelButtons = categoryModalElement ? categoryModalElement.querySelectorAll('[data-category-modal-cancel]') : [];
    const skillModalCancelButtons = skillModalElement ? skillModalElement.querySelectorAll('[data-skill-modal-cancel]') : [];

    let categoryKeyCounter = 0;
    let skillKeyCounter = 0;

    function parseStructure(raw) {
        if (!raw) {
            return [];
        }

        try {
            const parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            console.warn('Failed to parse skills structure payload', error);
            return [];
        }
    }

    function normalizeBoolean(value) {
        if (typeof value === 'boolean') {
            return value;
        }
        if (typeof value === 'number') {
            return value === 1;
        }
        if (typeof value === 'string') {
            const trimmed = value.trim().toLowerCase();
            return trimmed === '1' || trimmed === 'true' || trimmed === 'yes' || trimmed === 'on';
        }
        return false;
    }

    function clampPercent(value) {
        const numeric = Number(value);
        if (Number.isNaN(numeric)) {
            return 0;
        }
        if (numeric <= 0) {
            return 0;
        }
        if (numeric >= 100) {
            return 100;
        }
        return Math.round(numeric);
    }

    function nextCategoryKey() {
        categoryKeyCounter += 1;
        return 'cat-temp-' + categoryKeyCounter;
    }

    function nextSkillKey() {
        skillKeyCounter += 1;
        return 'skill-temp-' + skillKeyCounter;
    }

    function cloneSkill(skill, categoryKey) {
        if (!skill) {
            return null;
        }

        const clone = {
            id: Number(skill.id || 0),
            category_id: Number(skill.category_id || 0),
            name: typeof skill.name === 'string' ? skill.name : '',
            proficiency_level: clampPercent(skill.proficiency_level !== undefined ? skill.proficiency_level : skill.proficiency),
            is_visible: normalizeBoolean(skill.is_visible || skill.visible)
        };

        clone.clientKey = skill.clientKey || (clone.id > 0 ? 'skill-' + clone.id : nextSkillKey());
        clone.categoryClientKey = categoryKey;

        return clone;
    }

    function cloneCategory(category) {
        if (!category) {
            return null;
        }

        const clone = {
            id: Number(category.id || 0),
            title: typeof category.title === 'string' ? category.title : (category.name || ''),
            icon_class: typeof category.icon_class === 'string' ? category.icon_class : '',
            is_visible: normalizeBoolean(category.is_visible || category.visible)
        };

        clone.clientKey = category.clientKey || (clone.id > 0 ? 'cat-' + clone.id : nextCategoryKey());
        const skills = Array.isArray(category.skills) ? category.skills : [];
        clone.skills = skills.map(function (entry) {
            return cloneSkill(entry, clone.clientKey);
        }).filter(Boolean);

        return clone;
    }

    const initialStructure = parseStructure(container.dataset.skillsInitial);
    const state = {
        categories: initialStructure.map(cloneCategory).filter(Boolean),
        saved: initialStructure.map(cloneCategory).filter(Boolean)
    };

    function updateCountersFromState() {
        state.categories.forEach(function (category) {
            if (category.clientKey && category.clientKey.indexOf('cat-temp-') === 0) {
                const numeric = Number(category.clientKey.replace('cat-temp-', ''));
                if (!Number.isNaN(numeric)) {
                    categoryKeyCounter = Math.max(categoryKeyCounter, numeric);
                }
            }

            category.skills.forEach(function (skill) {
                if (skill.clientKey && skill.clientKey.indexOf('skill-temp-') === 0) {
                    const numeric = Number(skill.clientKey.replace('skill-temp-', ''));
                    if (!Number.isNaN(numeric)) {
                        skillKeyCounter = Math.max(skillKeyCounter, numeric);
                    }
                }
            });
        });
    }

    updateCountersFromState();

    function findCategoryByKey(key) {
        if (!key) {
            return null;
        }

        return state.categories.find(function (category) {
            return category.clientKey === key;
        }) || null;
    }

    function findSkillByKey(category, key) {
        if (!category || !key) {
            return null;
        }

        return category.skills.find(function (skill) {
            return skill.clientKey === key;
        }) || null;
    }

    function renderSkillItems(category, listElement) {
        if (!listElement) {
            return;
        }

        listElement.innerHTML = '';

        const skills = Array.isArray(category.skills) ? category.skills : [];

        if (!skills.length) {
            const placeholder = document.createElement('div');
            placeholder.className = 'text-muted small py-2';
            placeholder.dataset.skillEmpty = 'true';
            placeholder.textContent = 'No skills added yet.';
            listElement.appendChild(placeholder);
            return;
        }

        skills.forEach(function (skill) {
            const fragment = skillTemplate.content.cloneNode(true);
            const item = fragment.querySelector('[data-skill-item]');
            if (!item) {
                return;
            }

            item.dataset.skillKey = skill.clientKey;
            item.dataset.skillId = String(skill.id || 0);
            item.dataset.sortableId = skill.clientKey;
            item.dataset.categoryKey = category.clientKey;
            item.classList.toggle('is-hidden', !skill.is_visible);

            const nameElement = fragment.querySelector('[data-skill-name]');
            if (nameElement) {
                nameElement.textContent = skill.name || 'Skill';
            }

            const percentElement = fragment.querySelector('[data-skill-percent]');
            if (percentElement) {
                percentElement.textContent = clampPercent(skill.proficiency_level) + '%';
            }

            const hiddenBadge = fragment.querySelector('[data-skill-hidden-badge]');
            if (hiddenBadge) {
                hiddenBadge.classList.toggle('d-none', skill.is_visible);
            }

            const toggleElement = fragment.querySelector('[data-skill-visible-toggle]');
            if (toggleElement) {
                toggleElement.checked = !!skill.is_visible;
            }

            const editButton = fragment.querySelector('[data-skill-edit]');
            if (editButton) {
                editButton.dataset.categoryKey = category.clientKey;
                editButton.dataset.skillKey = skill.clientKey;
            }

            const deleteButton = fragment.querySelector('[data-skill-delete]');
            if (deleteButton) {
                deleteButton.dataset.categoryKey = category.clientKey;
                deleteButton.dataset.skillKey = skill.clientKey;
            }

            listElement.appendChild(fragment);
        });

        initializeSortableLists();
    }

    function renderCategories() {
        categoryList.innerHTML = '';

        const hasCategories = state.categories.length > 0;

        if (categoryEmpty) {
            categoryEmpty.classList.toggle('d-none', hasCategories);
        }

        if (!hasCategories) {
            return;
        }

        state.categories.forEach(function (category) {
            const fragment = categoryTemplate.content.cloneNode(true);
            const item = fragment.querySelector('[data-category-item]');
            if (!item) {
                return;
            }

            item.dataset.categoryKey = category.clientKey;
            item.dataset.categoryId = String(category.id || 0);
            item.dataset.sortableId = category.clientKey;
            item.classList.toggle('is-hidden', !category.is_visible);

            const titleElement = fragment.querySelector('[data-category-title]');
            if (titleElement) {
                titleElement.textContent = category.title || 'Untitled Category';
            }

            const hiddenBadge = fragment.querySelector('[data-category-hidden-badge]');
            if (hiddenBadge) {
                hiddenBadge.classList.toggle('d-none', category.is_visible);
            }

            const iconElement = fragment.querySelector('[data-category-icon]');
            if (iconElement) {
                const icon = (category.icon_class || '').trim();
                if (icon) {
                    iconElement.textContent = icon;
                    iconElement.classList.remove('d-none');
                } else {
                    iconElement.textContent = '';
                    iconElement.classList.add('d-none');
                }
            }

            const visibleToggle = fragment.querySelector('[data-category-visible-toggle]');
            if (visibleToggle) {
                visibleToggle.checked = !!category.is_visible;
            }

            const skillList = fragment.querySelector('[data-skill-list]');
            if (skillList) {
                skillList.dataset.categoryKey = category.clientKey;
                renderSkillItems(category, skillList);
            }

            const addSkillButton = fragment.querySelector('[data-skill-add]');
            if (addSkillButton) {
                addSkillButton.dataset.categoryKey = category.clientKey;
            }

            categoryList.appendChild(fragment);
        });

        initializeSortableLists();
    }

    function resolveIconClass(category) {
        const icon = (category.icon_class || '').trim();
        if (!icon) {
            return 'bi bi-palette';
        }
        if (icon.indexOf(' ') !== -1) {
            return icon;
        }
        if (icon.indexOf('bi-') === 0) {
            return 'bi ' + icon;
        }
        return icon;
    }

    function renderPreview() {
        if (previewGrid) {
            previewGrid.innerHTML = '';
        }

        const hasCategories = state.categories.length > 0;

        if (previewEmpty) {
            previewEmpty.classList.toggle('d-none', hasCategories);
        }

        if (!hasCategories || !previewGrid) {
            return;
        }

        state.categories.forEach(function (category) {
            const fragment = previewCategoryTemplate.content.cloneNode(true);
            const wrapper = fragment.querySelector('[data-preview-category]');
            if (!wrapper) {
                return;
            }

            wrapper.dataset.categoryKey = category.clientKey;
            wrapper.classList.toggle('is-hidden', !category.is_visible);

            const titleElement = fragment.querySelector('[data-preview-title]');
            if (titleElement) {
                titleElement.textContent = category.title || 'Category';
            }

            const hiddenBadge = fragment.querySelector('[data-preview-hidden-badge]');
            if (hiddenBadge) {
                hiddenBadge.classList.toggle('d-none', category.is_visible);
            }

            const iconWrapper = fragment.querySelector('[data-preview-icon-wrapper]');
            const iconElement = fragment.querySelector('[data-preview-icon]');
            if (iconElement) {
                const resolvedIcon = resolveIconClass(category);
                iconElement.className = resolvedIcon;
                if (iconWrapper) {
                    iconWrapper.classList.toggle('skills-preview-icon--default', resolvedIcon === 'bi bi-palette');
                }
            }

            const skillList = fragment.querySelector('[data-preview-skill-list]');
            const skills = Array.isArray(category.skills) ? category.skills : [];

            if (skillList) {
                skillList.innerHTML = '';

                if (!skills.length) {
                    const empty = document.createElement('p');
                    empty.className = 'text-muted small mb-0';
                    empty.textContent = 'No skills available.';
                    skillList.appendChild(empty);
                } else {
                    skills.forEach(function (skill) {
                        const skillFragment = previewSkillTemplate.content.cloneNode(true);
                        const skillWrapper = skillFragment.querySelector('[data-preview-skill]');
                        if (skillWrapper) {
                            skillWrapper.classList.toggle('is-hidden', !skill.is_visible);
                        }

                        const nameElement = skillFragment.querySelector('[data-preview-skill-name]');
                        if (nameElement) {
                            nameElement.textContent = skill.name || 'Skill';
                        }

                        const percentElement = skillFragment.querySelector('[data-preview-skill-percent]');
                        const pct = clampPercent(skill.proficiency_level);
                        if (percentElement) {
                            percentElement.textContent = pct + '%';
                        }

                        const barElement = skillFragment.querySelector('[data-preview-skill-bar]');
                        if (barElement) {
                            barElement.style.width = pct + '%';
                        }

                        skillList.appendChild(skillFragment);
                    });
                }
            }

            previewGrid.appendChild(fragment);
        });
    }

    function serializeForPayload() {
        return state.categories.map(function (category) {
            return {
                id: category.id > 0 ? category.id : 0,
                title: category.title,
                icon_class: category.icon_class,
                is_visible: category.is_visible ? 1 : 0,
                skills: category.skills.map(function (skill) {
                    return {
                        id: skill.id > 0 ? skill.id : 0,
                        name: skill.name,
                        proficiency_level: clampPercent(skill.proficiency_level),
                        is_visible: skill.is_visible ? 1 : 0
                    };
                })
            };
        });
    }

    function updatePayload() {
        if (!payloadInput) {
            return;
        }

        payloadInput.value = JSON.stringify(serializeForPayload());
    }

    function updateFeedback() {
        if (!feedback) {
            return;
        }

        const totalCategories = state.categories.length;
        const totalSkills = state.categories.reduce(function (count, category) {
            return count + category.skills.length;
        }, 0);

        if (!totalCategories && !totalSkills) {
            feedback.textContent = '';
            return;
        }

        const categoryLabel = totalCategories === 1 ? 'category' : 'categories';
        const skillLabel = totalSkills === 1 ? 'skill' : 'skills';

        feedback.textContent = totalCategories + ' ' + categoryLabel + ', ' + totalSkills + ' ' + skillLabel + ' total.';
    }

    function renderAll() {
        renderCategories();
        renderPreview();
        updatePayload();
        updateFeedback();
    }

    function clearCategoryModalError() {
        if (!categoryModalError) {
            return;
        }
        categoryModalError.classList.add('d-none');
        categoryModalError.textContent = '';
    }

    function showCategoryModalError(message) {
        if (!categoryModalError) {
            window.alert(message);
            return;
        }
        categoryModalError.textContent = message;
        categoryModalError.classList.remove('d-none');
    }

    function clearSkillModalError() {
        if (!skillModalError) {
            return;
        }
        skillModalError.classList.add('d-none');
        skillModalError.textContent = '';
    }

    function showSkillModalError(message) {
        if (!skillModalError) {
            window.alert(message);
            return;
        }
        skillModalError.textContent = message;
        skillModalError.classList.remove('d-none');
    }

    function collectCategoryFormValues() {
        if (!categoryForm) {
            return { valid: false, error: 'Form not available.' };
        }

        const titleInput = categoryForm.querySelector('[data-category-field="title"]');
        const iconInput = categoryForm.querySelector('[data-category-field="icon"]');
        const visibleInput = categoryForm.querySelector('[data-category-field="visible"]');

        const title = titleInput ? titleInput.value.trim() : '';
        const iconRaw = iconInput ? iconInput.value.trim() : '';
        const iconSanitized = iconRaw.replace(/[^a-z0-9\s\-_:]/gi, '').replace(/\s+/g, ' ').trim();
        const visible = visibleInput ? !!visibleInput.checked : true;

        if (title.length < 2) {
            return { valid: false, error: 'Category title must be at least 2 characters long.' };
        }

        return {
            valid: true,
            title: title.substring(0, 150),
            icon: iconSanitized.substring(0, 120),
            visible: visible
        };
    }

    function collectSkillFormValues() {
        if (!skillForm) {
            return { valid: false, error: 'Form not available.' };
        }

        const nameInput = skillForm.querySelector('[data-skill-field="name"]');
        const proficiencyInput = skillForm.querySelector('[data-skill-field="proficiency"]');
        const visibleInput = skillForm.querySelector('[data-skill-field="visible"]');

        const name = nameInput ? nameInput.value.trim() : '';
        const proficiency = proficiencyInput ? clampPercent(proficiencyInput.value) : 0;
        const visible = visibleInput ? !!visibleInput.checked : true;

        if (name.length < 2) {
            return { valid: false, error: 'Skill name must be at least 2 characters long.' };
        }

        return {
            valid: true,
            name: name.substring(0, 150),
            proficiency: proficiency,
            visible: visible
        };
    }

    function openCategoryModal(category) {
        if (!categoryModal || !categoryForm) {
            window.alert('Category modal is not available.');
            return;
        }

        categoryForm.reset();
        clearCategoryModalError();

        if (category) {
            categoryForm.dataset.mode = 'edit';
            categoryForm.dataset.categoryKey = category.clientKey;
            if (categoryModalTitle) {
                categoryModalTitle.textContent = 'Edit Category';
            }

            const titleInput = categoryForm.querySelector('[data-category-field="title"]');
            if (titleInput) {
                titleInput.value = category.title || '';
            }

            const iconInput = categoryForm.querySelector('[data-category-field="icon"]');
            if (iconInput) {
                iconInput.value = category.icon_class || '';
            }

            const visibleInput = categoryForm.querySelector('[data-category-field="visible"]');
            if (visibleInput) {
                visibleInput.checked = !!category.is_visible;
            }
        } else {
            categoryForm.dataset.mode = 'create';
            categoryForm.dataset.categoryKey = '';
            if (categoryModalTitle) {
                categoryModalTitle.textContent = 'Add Category';
            }

            const visibleInput = categoryForm.querySelector('[data-category-field="visible"]');
            if (visibleInput) {
                visibleInput.checked = true;
            }
        }

        categoryModal.show();

        const titleInput = categoryForm.querySelector('[data-category-field="title"]');
        if (titleInput) {
            window.setTimeout(function () {
                titleInput.focus();
                titleInput.select();
            }, 210);
        }
    }

    function openSkillModal(categoryKey, skill) {
        if (!skillModal || !skillForm) {
            window.alert('Skill modal is not available.');
            return;
        }

        skillForm.reset();
        clearSkillModalError();
        skillForm.dataset.categoryKey = categoryKey || '';
        skillForm.dataset.skillKey = skill ? skill.clientKey : '';
        skillForm.dataset.mode = skill ? 'edit' : 'create';

        if (skillModalTitle) {
            skillModalTitle.textContent = skill ? 'Edit Skill' : 'Add Skill';
        }

        if (skill) {
            const nameInput = skillForm.querySelector('[data-skill-field="name"]');
            if (nameInput) {
                nameInput.value = skill.name || '';
            }

            const proficiencyInput = skillForm.querySelector('[data-skill-field="proficiency"]');
            if (proficiencyInput) {
                proficiencyInput.value = clampPercent(skill.proficiency_level);
            }

            const visibleInput = skillForm.querySelector('[data-skill-field="visible"]');
            if (visibleInput) {
                visibleInput.checked = !!skill.is_visible;
            }
        } else {
            const visibleInput = skillForm.querySelector('[data-skill-field="visible"]');
            if (visibleInput) {
                visibleInput.checked = true;
            }
        }

        skillModal.show();

        const nameInput = skillForm.querySelector('[data-skill-field="name"]');
        if (nameInput) {
            window.setTimeout(function () {
                nameInput.focus();
                nameInput.select();
            }, 210);
        }
    }

    function handleCategoryDeletion(category) {
        if (!category) {
            return;
        }

        const confirmed = window.confirm('Delete category "' + (category.title || 'Untitled Category') + '" and all nested skills?');
        if (!confirmed) {
            return;
        }

        state.categories = state.categories.filter(function (entry) {
            return entry.clientKey !== category.clientKey;
        });

        renderAll();
    }

    function handleSkillDeletion(category, skill) {
        if (!category || !skill) {
            return;
        }

        const confirmed = window.confirm('Remove "' + (skill.name || 'Untitled Skill') + '" from ' + (category.title || 'this category') + '?');
        if (!confirmed) {
            return;
        }

        category.skills = category.skills.filter(function (entry) {
            return entry.clientKey !== skill.clientKey;
        });

        renderAll();
    }

    function handleCategoryReorder(listElement) {
        const nodes = listElement.querySelectorAll('[data-category-item]');
        if (!nodes.length) {
            return;
        }

        const ordered = [];
        nodes.forEach(function (node) {
            const key = node.dataset.categoryKey || '';
            const category = findCategoryByKey(key);
            if (category && ordered.indexOf(category) === -1) {
                ordered.push(category);
            }
        });

        if (ordered.length !== state.categories.length) {
            return;
        }

        state.categories = ordered;
        updatePayload();
        renderPreview();
        updateFeedback();
    }

    function handleSkillReorder(listElement) {
        const categoryKey = listElement.dataset.categoryKey || '';
        const category = findCategoryByKey(categoryKey);
        if (!category) {
            return;
        }

        const nodes = listElement.querySelectorAll('[data-skill-item]');
        const ordered = [];

        nodes.forEach(function (node) {
            const key = node.dataset.skillKey || '';
            const skill = findSkillByKey(category, key);
            if (skill && ordered.indexOf(skill) === -1) {
                ordered.push(skill);
            }
        });

        if (ordered.length !== category.skills.length) {
            return;
        }

        category.skills = ordered;
        updatePayload();
        renderPreview();
        updateFeedback();
    }

    addCategoryButtons.forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            openCategoryModal(null);
        });
    });

    cancelButtons.forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            state.categories = state.saved.map(cloneCategory).filter(Boolean);
            renderAll();
        });
    });

    saveButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            updatePayload();
        });
    });

    if (form) {
        form.addEventListener('submit', function () {
            updatePayload();
        });
    }

    if (categoryForm) {
        categoryForm.addEventListener('submit', function (event) {
            event.preventDefault();
            clearCategoryModalError();

            const values = collectCategoryFormValues();
            if (!values.valid) {
                showCategoryModalError(values.error);
                return;
            }

            const mode = categoryForm.dataset.mode || 'create';
            const categoryKey = categoryForm.dataset.categoryKey || '';

            if (mode === 'edit') {
                const category = findCategoryByKey(categoryKey);
                if (!category) {
                    showCategoryModalError('Unable to locate the selected category.');
                    return;
                }

                category.title = values.title;
                category.icon_class = values.icon;
                category.is_visible = values.visible;
            } else {
                const newCategory = {
                    id: 0,
                    clientKey: nextCategoryKey(),
                    title: values.title,
                    icon_class: values.icon,
                    is_visible: values.visible,
                    skills: []
                };
                state.categories.push(newCategory);
            }

            if (categoryModal) {
                categoryModal.hide();
            }

            renderAll();
        });
    }

    categoryModalCancelButtons.forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            clearCategoryModalError();
            if (categoryModal) {
                categoryModal.hide();
            }
        });
    });

    if (skillForm) {
        skillForm.addEventListener('submit', function (event) {
            event.preventDefault();
            clearSkillModalError();

            const values = collectSkillFormValues();
            if (!values.valid) {
                showSkillModalError(values.error);
                return;
            }

            const categoryKey = skillForm.dataset.categoryKey || '';
            const skillKey = skillForm.dataset.skillKey || '';
            const category = findCategoryByKey(categoryKey);
            if (!category) {
                showSkillModalError('Unable to locate the selected category.');
                return;
            }

            const mode = skillForm.dataset.mode || 'create';
            if (mode === 'edit') {
                const skill = findSkillByKey(category, skillKey);
                if (!skill) {
                    showSkillModalError('Unable to locate the selected skill.');
                    return;
                }

                skill.name = values.name;
                skill.proficiency_level = values.proficiency;
                skill.is_visible = values.visible;
            } else {
                const newSkill = {
                    id: 0,
                    clientKey: nextSkillKey(),
                    categoryClientKey: category.clientKey,
                    name: values.name,
                    proficiency_level: values.proficiency,
                    is_visible: values.visible
                };
                category.skills.push(newSkill);
            }

            if (skillModal) {
                skillModal.hide();
            }

            renderAll();
        });
    }

    skillModalCancelButtons.forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            clearSkillModalError();
            if (skillModal) {
                skillModal.hide();
            }
        });
    });

    categoryList.addEventListener('click', function (event) {
        const editCategoryButton = event.target.closest('[data-category-edit]');
        if (editCategoryButton) {
            event.preventDefault();
            const categoryItem = editCategoryButton.closest('[data-category-item]');
            const categoryKey = categoryItem ? categoryItem.dataset.categoryKey : '';
            const category = findCategoryByKey(categoryKey);
            openCategoryModal(category || null);
            return;
        }

        const deleteCategoryButton = event.target.closest('[data-category-delete]');
        if (deleteCategoryButton) {
            event.preventDefault();
            const categoryItem = deleteCategoryButton.closest('[data-category-item]');
            const categoryKey = categoryItem ? categoryItem.dataset.categoryKey : '';
            const category = findCategoryByKey(categoryKey);
            handleCategoryDeletion(category || null);
            return;
        }

        const addSkillButton = event.target.closest('[data-skill-add]');
        if (addSkillButton) {
            event.preventDefault();
            const categoryKey = addSkillButton.dataset.categoryKey || '';
            const category = findCategoryByKey(categoryKey);
            if (!category) {
                return;
            }
            openSkillModal(category.clientKey, null);
            return;
        }

        const editSkillButton = event.target.closest('[data-skill-edit]');
        if (editSkillButton) {
            event.preventDefault();
            const categoryKey = editSkillButton.dataset.categoryKey || '';
            const skillKey = editSkillButton.dataset.skillKey || '';
            const category = findCategoryByKey(categoryKey);
            const skill = findSkillByKey(category, skillKey);
            if (!category || !skill) {
                return;
            }
            openSkillModal(category.clientKey, skill);
            return;
        }

        const deleteSkillButton = event.target.closest('[data-skill-delete]');
        if (deleteSkillButton) {
            event.preventDefault();
            const categoryKey = deleteSkillButton.dataset.categoryKey || '';
            const skillKey = deleteSkillButton.dataset.skillKey || '';
            const category = findCategoryByKey(categoryKey);
            const skill = findSkillByKey(category, skillKey);
            if (!category || !skill) {
                return;
            }
            handleSkillDeletion(category, skill);
        }
    });

    categoryList.addEventListener('change', function (event) {
        const categoryItem = event.target.closest('[data-category-item]');
        if (!categoryItem) {
            return;
        }

        const categoryKey = categoryItem.dataset.categoryKey || '';
        const category = findCategoryByKey(categoryKey);
        if (!category) {
            return;
        }

        if (event.target.matches('[data-category-visible-toggle]')) {
            category.is_visible = !!event.target.checked;
            const hiddenBadge = categoryItem.querySelector('[data-category-hidden-badge]');
            if (hiddenBadge) {
                hiddenBadge.classList.toggle('d-none', category.is_visible);
            }
            categoryItem.classList.toggle('is-hidden', !category.is_visible);
            renderPreview();
            updatePayload();
            updateFeedback();
            return;
        }

        if (event.target.matches('[data-skill-visible-toggle]')) {
            const skillItem = event.target.closest('[data-skill-item]');
            const skillKey = skillItem ? skillItem.dataset.skillKey : '';
            const skill = findSkillByKey(category, skillKey);
            if (!skill) {
                return;
            }

            skill.is_visible = !!event.target.checked;
            if (skillItem) {
                const hiddenBadge = skillItem.querySelector('[data-skill-hidden-badge]');
                if (hiddenBadge) {
                    hiddenBadge.classList.toggle('d-none', skill.is_visible);
                }
                skillItem.classList.toggle('is-hidden', !skill.is_visible);
            }

            renderPreview();
            updatePayload();
            updateFeedback();
        }
    });

    container.addEventListener('sortable:reordered', function (event) {
        if (event.target === categoryList) {
            handleCategoryReorder(event.target);
        } else if (event.target.matches('[data-skill-list]')) {
            handleSkillReorder(event.target);
        }
    });

    renderAll();
}

function initializeAboutHighlights() {
    const list = document.querySelector('[data-about-highlights-list]');
    if (!list) {
        return;
    }

    const addButton = document.querySelector('[data-about-highlight-add]');
    const addInput = document.querySelector('[data-about-highlight-input]');
    const previewContainer = document.querySelector('[data-about-highlight-preview]');
    const emptyState = document.querySelector('[data-about-highlight-empty]');

    function collectHighlightValues() {
        const inputs = list.querySelectorAll('[data-about-highlight-field]');
        return Array.from(inputs).map(function (input) {
            return (input.value || '').trim();
        }).filter(function (value) {
            return value.length > 0;
        });
    }

    function refreshPreview() {
        const values = collectHighlightValues();

        if (previewContainer) {
            previewContainer.innerHTML = '';

            if (values.length) {
                values.slice(0, 6).forEach(function (value) {
                    const chip = document.createElement('span');
                    chip.className = 'badge bg-primary-subtle text-primary';
                    chip.textContent = value;
                    previewContainer.appendChild(chip);
                });

                if (values.length > 6) {
                    const more = document.createElement('span');
                    more.className = 'badge bg-secondary-subtle text-secondary';
                    more.textContent = `+${values.length - 6}`;
                    previewContainer.appendChild(more);
                }
            }
        }

        if (emptyState) {
            emptyState.classList.toggle('d-none', values.length > 0);
        }
    }

    function bindHighlightInput(input) {
        if (!input) {
            return;
        }

        input.addEventListener('input', refreshPreview);
        input.addEventListener('blur', refreshPreview);
    }

    function bindRemoveButton(button) {
        if (!button) {
            return;
        }

        button.addEventListener('click', function () {
            const item = button.closest('[data-sortable-item]');
            if (item) {
                item.remove();
                refreshPreview();
            }
        });
    }

    function createHighlightItem(text) {
        const item = document.createElement('li');
        item.className = 'list-group-item d-flex align-items-center gap-3 flex-wrap';
        item.setAttribute('data-sortable-item', '');

        const safeText = escapeHtml(text || '');
        item.innerHTML = `
            <span class="sortable-handle text-muted" title="Drag to reorder" data-sortable-handle>
                <i class="bi bi-grip-vertical"></i>
            </span>
            <div class="flex-grow-1">
                <label class="visually-hidden">Highlight</label>
                <input type="text"
                       class="form-control form-control-sm"
                       name="about_highlights[]"
                       value="${safeText}"
                       placeholder="Headline-worthy impact"
                       data-about-highlight-field>
            </div>
            <button type="button" class="btn btn-outline-danger btn-sm" title="Remove highlight" data-about-highlight-remove>
                <i class="bi bi-trash"></i>
            </button>
        `;

        list.appendChild(item);

        bindHighlightInput(item.querySelector('[data-about-highlight-field]'));
        bindRemoveButton(item.querySelector('[data-about-highlight-remove]'));

        refreshPreview();
    }

    list.querySelectorAll('[data-about-highlight-field]').forEach(bindHighlightInput);
    list.querySelectorAll('[data-about-highlight-remove]').forEach(bindRemoveButton);

    if (addInput) {
        addInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                if (addButton) {
                    addButton.click();
                }
            }
        });
    }

    if (addButton) {
        addButton.addEventListener('click', function () {
            if (!addInput) {
                return;
            }

            const value = (addInput.value || '').trim();
            if (!value) {
                addInput.focus();
                return;
            }

            createHighlightItem(value);
            addInput.value = '';
            addInput.focus();
        });
    }

    list.addEventListener('input', function (event) {
        if (event.target && event.target.matches('[data-about-highlight-field]')) {
            refreshPreview();
        }
    });

    list.addEventListener('sortable:reordered', refreshPreview);

    refreshPreview();
}

function initializeTimelineForm() {
    const form = document.querySelector('[data-timeline-form]');
    if (!form) {
        return;
    }

    const metaInputs = form.querySelectorAll('[data-timeline-preview-meta]');
    const metaTarget = form.querySelector('.preview-timeline-item-meta');
    const typeToggle = form.querySelector('[data-timeline-type-toggle]');
    const typeBadge = form.querySelector('[data-timeline-type-badge]');
    const typeMarker = form.querySelector('[data-timeline-type-marker]');

    const refreshMeta = function () {
        if (!metaTarget) {
            return;
        }

        const parts = [];
        metaInputs.forEach(function (input) {
            const value = (input.value || '').trim();
            if (value) {
                parts.push(value);
            }
        });

    metaTarget.textContent = parts.join(' | ');
    };

    const updateType = function () {
        if (!typeBadge) {
            return;
        }

        const isEducation = !!(typeToggle && typeToggle.checked);

        typeBadge.classList.add('rounded-pill');

        if (isEducation) {
            typeBadge.textContent = 'Education';
            typeBadge.classList.remove('bg-light', 'text-muted');
            typeBadge.classList.add('bg-info-subtle', 'text-info');
            if (typeMarker) {
                typeMarker.classList.add('timeline-preview-marker-education');
            }
        } else {
            typeBadge.textContent = 'Experience';
            typeBadge.classList.remove('bg-info-subtle', 'text-info');
            typeBadge.classList.add('bg-light', 'text-muted');
            if (typeMarker) {
                typeMarker.classList.remove('timeline-preview-marker-education');
            }
        }
    };

    metaInputs.forEach(function (input) {
        input.addEventListener('input', refreshMeta);
    });

    if (typeToggle) {
        typeToggle.addEventListener('change', updateType);
    }

    refreshMeta();
    updateType();
}

function initializeTimelineManager() {
    const manager = document.querySelector('[data-timeline-manager]');
    if (!manager || manager.dataset.timelineManagerBound === 'true') {
        return;
    }

    manager.dataset.timelineManagerBound = 'true';

    const list = manager.querySelector('[data-timeline-list]');
    const feedback = manager.querySelector('[data-timeline-feedback]');
    const visibleCountLabel = manager.querySelector('[data-timeline-visible-count]');
    const emptyState = manager.querySelector('[data-timeline-empty]');
    const resetButton = manager.querySelector('[data-timeline-reset-order]');
    const filters = manager.querySelectorAll('[data-timeline-filter]');
    const filterReset = manager.querySelector('[data-timeline-filter-reset]');
    const orderForm = manager.querySelector('[data-timeline-order-form]');
    const orderInput = orderForm ? orderForm.querySelector('#timelineOrderInput') : null;
    const saveButton = orderForm ? orderForm.querySelector('#timelineOrderSubmit') : null;
    const orderFeedback = orderForm ? orderForm.querySelector('[data-timeline-order-feedback]') : null;
    const reorderUrl = manager.dataset.timelineReorderUrl || (orderForm ? orderForm.getAttribute('action') : '');

    const previewTarget = manager.dataset.timelinePreviewTarget || '';
    const previewRoot = previewTarget ? document.querySelector(previewTarget) : null;
    const previewGroupsContainer = previewRoot ? previewRoot.querySelector('[data-timeline-preview-groups]') : null;
    const previewEmpty = previewRoot ? previewRoot.querySelector('[data-timeline-preview-empty]') : null;
    const groupTemplate = document.getElementById('timelinePreviewGroupTemplate');
    const itemTemplate = document.getElementById('timelinePreviewItemTemplate');

    let orderFeedbackTimer = null;

    const state = {
        items: parseTimelineItems(manager.dataset.timelineItems),
        filter: {
            status: 'all',
            type: 'all'
        },
        originalOrder: orderInput ? (orderInput.defaultValue || orderInput.value || '') : (manager.dataset.timelineInitialOrder || ''),
        busy: false
    };

    if (!state.originalOrder && list) {
        state.originalOrder = serializeListOrder(list);
    }

    if (orderInput) {
        if (!orderInput.value) {
            orderInput.value = state.originalOrder;
        }
        orderInput.defaultValue = orderInput.defaultValue || state.originalOrder;
        orderInput.dataset.sortableReference = state.originalOrder;
    }

    applyFilters();
    renderPreview();
    updateButtonsDisabled();

    Array.prototype.forEach.call(filters, function (button) {
        button.addEventListener('click', function () {
            const key = button.dataset.timelineFilter || '';
            if (!key) {
                return;
            }

            const value = button.dataset.filterValue || 'all';

            if (state.filter[key] === value) {
                if (value !== 'all') {
                    state.filter[key] = 'all';
                    updateFilterButtons(key, 'all');
                    applyFilters();
                    renderPreview();
                }
                return;
            }

            state.filter[key] = value;
            updateFilterButtons(key, value);
            applyFilters();
            renderPreview();
        });
    });

    if (filterReset) {
        filterReset.addEventListener('click', function () {
            state.filter.status = 'all';
            state.filter.type = 'all';
            updateFilterButtons('status', 'all');
            updateFilterButtons('type', 'all');
            applyFilters();
            renderPreview();
        });
    }

    if (list) {
        list.addEventListener('sortable:reordered', function () {
            const ids = getCurrentOrderIds();
            if (ids.length) {
                state.items = reorderItems(state.items, ids);
            }

            renderPreview();
            updateVisibleCount(getVisibleCount());
            updateButtonsDisabled();
        });

        list.addEventListener('dragstart', function (event) {
            if (filtersActive() || state.busy) {
                event.preventDefault();
                setFeedback('Clear filters to adjust the timeline order.', 'warning');
            }
        }, true);
    }

    if (resetButton) {
        resetButton.addEventListener('click', function () {
            if (!list || !state.originalOrder || state.busy || filtersActive()) {
                return;
            }

            const ids = state.originalOrder.split(',').map(function (value) {
                return value.trim();
            }).filter(function (value) {
                return value !== '';
            });

            if (!ids.length) {
                return;
            }

            ids.forEach(function (id) {
                const item = list.querySelector('[data-timeline-item-id="' + id + '"]');
                if (item) {
                    list.appendChild(item);
                }
            });

            if (orderInput) {
                orderInput.value = state.originalOrder;
            }

            list.dispatchEvent(new CustomEvent('sortable:reordered', { bubbles: true }));
            setOrderFeedback('Order restored to the last saved state.', 'info');
            if (saveButton) {
                saveButton.disabled = true;
            }
        });
    }

    if (orderForm && orderInput) {
        orderForm.addEventListener('submit', function (event) {
            event.preventDefault();

            if (state.busy) {
                return;
            }

            if (filtersActive()) {
                setFeedback('Clear filters to adjust the timeline order.', 'warning');
                return;
            }

            const currentValue = orderInput.value || '';
            const baseline = orderInput.defaultValue || '';

            if (currentValue === baseline) {
                setOrderFeedback('No order changes detected.', 'info');
                return;
            }

            if (!reorderUrl) {
                setOrderFeedback('Reorder endpoint is missing.', 'danger');
                return;
            }

            submitOrder();
        });
    }

    function parseTimelineItems(raw) {
        if (!raw) {
            return [];
        }

        try {
            const parsed = JSON.parse(raw);
            if (Array.isArray(parsed)) {
                return parsed.map(normalizeTimelineItem).filter(Boolean).sort(function (a, b) {
                    if (a.sort_order !== b.sort_order) {
                        return a.sort_order - b.sort_order;
                    }
                    return a.id - b.id;
                });
            }
        } catch (error) {
            console.warn('Failed to parse timeline dataset', error);
        }

        return [];
    }

    function normalizeTimelineItem(source) {
        if (!source || typeof source !== 'object') {
            return null;
        }

        const id = Number(source.id || source.timeline_id || 0);
        if (!id) {
            return null;
        }

        let tags = source.tags;
        if (typeof tags === 'string') {
            try {
                const decoded = JSON.parse(tags);
                tags = Array.isArray(decoded) ? decoded : tags.split(',');
            } catch (error) {
                tags = tags.split(',');
            }
        }

        if (!Array.isArray(tags)) {
            tags = [];
        }

        tags = tags.map(function (value) {
            return String(value || '').trim();
        }).filter(function (value) {
            return value !== '';
        }).slice(0, 10);

        const organization = typeof source.organization === 'string'
            ? source.organization
            : (typeof source.company === 'string' ? source.company : '');

        const dateRange = typeof source.date_range === 'string'
            ? source.date_range
            : (typeof source.date === 'string' ? source.date : '');

        const description = typeof source.description === 'string' ? source.description : '';

        const status = typeof source.status === 'string' ? source.status.toLowerCase() : 'draft';

        return {
            id: id,
            title: typeof source.title === 'string' ? source.title : '',
            organization: organization,
            date_range: dateRange,
            description: description,
            tags: tags,
            is_education: Number(source.is_education || 0) === 1,
            status: status === 'published' ? 'published' : 'draft',
            sort_order: Number(source.sort_order || 0)
        };
    }

    function applyFilters() {
        if (!list) {
            updateVisibleCount(state.items.length);
            return;
        }

        const items = list.querySelectorAll('[data-timeline-item-id]');
        let visible = 0;

        items.forEach(function (item) {
            const status = (item.dataset.timelineStatus || 'draft').toLowerCase();
            const type = item.dataset.timelineType || 'experience';
            const matchesStatus = state.filter.status === 'all' || state.filter.status === status;
            const matchesType = state.filter.type === 'all' || state.filter.type === type;
            const shouldShow = matchesStatus && matchesType;

            item.classList.toggle('d-none', !shouldShow);

            if (shouldShow) {
                visible += 1;
            }
        });

        if (list) {
            list.classList.toggle('d-none', visible === 0);
        }

        if (emptyState) {
            emptyState.classList.toggle('d-none', visible > 0);
        }

        updateVisibleCount(visible);
        updateButtonsDisabled();
    }

    function updateVisibleCount(visible) {
        if (!visibleCountLabel) {
            return;
        }

        const total = state.items.length;
        const label = visible === 1 ? 'entry' : 'entries';
        visibleCountLabel.textContent = 'Showing ' + visible + ' of ' + total + ' ' + label;
    }

    function filtersActive() {
        return state.filter.status !== 'all' || state.filter.type !== 'all';
    }

    function updateButtonsDisabled() {
        const filtered = filtersActive();

        if (resetButton) {
            resetButton.disabled = filtered || state.busy || !state.originalOrder;
        }

        if (saveButton) {
            if (filtered || state.busy) {
                saveButton.disabled = true;
            } else if (orderInput) {
                const baseline = orderInput.dataset.sortableReference || orderInput.defaultValue || '';
                saveButton.disabled = (orderInput.value || '') === baseline;
            }
        }

        if (filtered) {
            setFeedback('Reordering is disabled while filters are applied. Clear filters to adjust ordering.', 'info');
        } else {
            setFeedback('', '');
        }
    }

    function updateFilterButtons(key, activeValue) {
        Array.prototype.forEach.call(filters, function (button) {
            if (button.dataset.timelineFilter !== key) {
                return;
            }

            const value = button.dataset.filterValue || 'all';
            if (activeValue === 'all') {
                button.classList.toggle('active', value === 'all');
            } else {
                button.classList.toggle('active', value === activeValue);
            }
        });
    }

    function getCurrentOrderIds() {
        if (!list) {
            return [];
        }

        const serialized = serializeListOrder(list);
        if (!serialized) {
            return [];
        }

        return serialized.split(',').map(function (value) {
            return Number(value.trim());
        }).filter(function (value) {
            return value > 0;
        });
    }

    function reorderItems(items, ids) {
        if (!ids.length) {
            return items.slice();
        }

        const lookup = new Map();
        items.forEach(function (item) {
            lookup.set(String(item.id), item);
        });

        const ordered = [];

        ids.forEach(function (id) {
            const key = String(id);
            if (lookup.has(key)) {
                ordered.push(lookup.get(key));
                lookup.delete(key);
            }
        });

        lookup.forEach(function (item) {
            ordered.push(item);
        });

        return ordered;
    }

    function renderPreview() {
        if (!previewRoot) {
            return;
        }

        if (!previewGroupsContainer) {
            return;
        }

        const published = state.items.filter(function (item) {
            return item.status === 'published';
        });

        const source = published.length ? published : state.items.slice();
        const grouped = groupItemsByType(source);

        previewGroupsContainer.innerHTML = '';

        let renderedCount = 0;

        Object.keys(grouped).forEach(function (key) {
            const items = grouped[key];
            if (!items.length) {
                return;
            }

            renderedCount += items.length;
            previewGroupsContainer.appendChild(buildPreviewGroup(key, items));
        });

        if (previewEmpty) {
            previewEmpty.classList.toggle('d-none', renderedCount > 0);
        }
    }

    function groupItemsByType(items) {
        const groups = {
            experience: [],
            education: []
        };

        items.forEach(function (item) {
            const key = item.is_education ? 'education' : 'experience';
            groups[key].push(item);
        });

        return groups;
    }

    function buildPreviewGroup(key, items) {
        const label = key === 'education' ? 'Education' : 'Experience';
        let element;

        if (groupTemplate && groupTemplate.content && groupTemplate.content.firstElementChild) {
            element = groupTemplate.content.firstElementChild.cloneNode(true);
            const title = element.querySelector('[data-preview-group-title]');
            const count = element.querySelector('[data-preview-group-count]');
            const stack = element.querySelector('[data-preview-group-stack]');

            if (title) {
                title.textContent = label;
            }
            if (count) {
                count.textContent = items.length + ' ' + (items.length === 1 ? 'entry' : 'entries');
            }
            if (stack) {
                stack.innerHTML = '';
                items.forEach(function (item, index) {
                    stack.appendChild(buildPreviewItem(item, index));
                });
            }
        } else {
            element = document.createElement('section');
            element.className = 'timeline-preview-group mb-4';

            const header = document.createElement('div');
            header.className = 'd-flex justify-content-between align-items-center mb-2';

            const title = document.createElement('h6');
            title.className = 'mb-0';
            title.textContent = label;

            const count = document.createElement('span');
            count.className = 'badge bg-light text-muted';
            count.textContent = items.length + ' ' + (items.length === 1 ? 'entry' : 'entries');

            header.appendChild(title);
            header.appendChild(count);
            element.appendChild(header);

            const stack = document.createElement('div');
            stack.className = 'timeline-preview-stack';
            items.forEach(function (item, index) {
                stack.appendChild(buildPreviewItem(item, index));
            });
            element.appendChild(stack);
        }

        return element;
    }

    function buildPreviewItem(item, index) {
        let element;

        if (itemTemplate && itemTemplate.content && itemTemplate.content.firstElementChild) {
            element = itemTemplate.content.firstElementChild.cloneNode(true);

            const marker = element.querySelector('[data-preview-item-marker]');
            const title = element.querySelector('[data-preview-item-title]');
            const meta = element.querySelector('[data-preview-item-meta]');
            const badge = element.querySelector('[data-preview-item-badge]');
            const description = element.querySelector('[data-preview-item-description]');
            const tagsContainer = element.querySelector('[data-preview-item-tags]');

            if (marker) {
                marker.classList.toggle('timeline-preview-marker-education', !!item.is_education);
            }
            if (title) {
                title.textContent = item.title || '';
            }
            if (meta) {
                const parts = [];
                if (item.organization) {
                    parts.push(item.organization);
                }
                if (item.date_range) {
                    parts.push(item.date_range);
                }
                meta.textContent = parts.join(' | ');
            }
            if (badge) {
                badge.textContent = item.is_education ? 'Education' : 'Experience';
            }
            if (description) {
                const text = truncateText(item.description, 140);
                description.textContent = text;
                description.classList.toggle('d-none', text === '');
            }
            if (tagsContainer) {
                tagsContainer.innerHTML = '';
                item.tags.slice(0, 4).forEach(function (tag) {
                    const badgeElement = document.createElement('span');
                    badgeElement.className = 'badge bg-primary-subtle text-primary';
                    badgeElement.textContent = tag;
                    tagsContainer.appendChild(badgeElement);
                });
            }
        } else {
            element = document.createElement('div');
            element.className = 'timeline-preview-item';
            if (index > 0) {
                element.classList.add('mt-3');
            }

            const marker = document.createElement('div');
            marker.className = 'timeline-preview-marker';
            if (item.is_education) {
                marker.classList.add('timeline-preview-marker-education');
            }
            element.appendChild(marker);

            const content = document.createElement('div');
            content.className = 'timeline-preview-content';
            element.appendChild(content);

            const header = document.createElement('div');
            header.className = 'd-flex justify-content-between align-items-start gap-2';
            content.appendChild(header);

            const textBlock = document.createElement('div');
            header.appendChild(textBlock);

            const title = document.createElement('h6');
            title.className = 'mb-1';
            title.textContent = item.title || '';
            textBlock.appendChild(title);

            const meta = document.createElement('div');
            meta.className = 'text-muted small';
            const parts = [];
            if (item.organization) {
                parts.push(item.organization);
            }
            if (item.date_range) {
                parts.push(item.date_range);
            }
            meta.textContent = parts.join(' | ');
            textBlock.appendChild(meta);

            const badge = document.createElement('span');
            badge.className = 'badge rounded-pill bg-light text-muted';
            badge.textContent = item.is_education ? 'Education' : 'Experience';
            header.appendChild(badge);

            if (item.description) {
                const paragraph = document.createElement('p');
                paragraph.className = 'small text-secondary mt-2 mb-2';
                paragraph.textContent = truncateText(item.description, 140);
                content.appendChild(paragraph);
            }

            if (item.tags.length) {
                const tagContainer = document.createElement('div');
                tagContainer.className = 'd-flex flex-wrap gap-2';
                item.tags.slice(0, 4).forEach(function (tag) {
                    const badgeElement = document.createElement('span');
                    badgeElement.className = 'badge bg-primary-subtle text-primary';
                    badgeElement.textContent = tag;
                    tagContainer.appendChild(badgeElement);
                });
                content.appendChild(tagContainer);
            }
        }

        return element;
    }

    function truncateText(value, limit) {
        if (typeof value !== 'string') {
            return '';
        }

        const trimmed = value.trim();
        if (trimmed === '') {
            return '';
        }

        if (trimmed.length <= limit) {
            return trimmed;
        }

        return trimmed.slice(0, limit).trimEnd() + '...';
    }

    function setFeedback(message, tone) {
        if (!feedback) {
            return;
        }

        feedback.classList.remove('d-none', 'alert-info', 'alert-warning', 'alert-danger', 'alert-success');

        if (!message) {
            feedback.textContent = '';
            feedback.classList.add('d-none', 'alert-info');
            return;
        }

        let alertClass = 'alert-info';
        if (tone === 'warning') {
            alertClass = 'alert-warning';
        } else if (tone === 'danger') {
            alertClass = 'alert-danger';
        } else if (tone === 'success') {
            alertClass = 'alert-success';
        }

        feedback.classList.add(alertClass);
        feedback.textContent = message;
    }

    function setOrderFeedback(message, tone) {
        if (!orderFeedback) {
            return;
        }

        if (orderFeedbackTimer) {
            window.clearTimeout(orderFeedbackTimer);
            orderFeedbackTimer = null;
        }

        orderFeedback.classList.remove('text-success', 'text-danger', 'text-warning', 'text-muted');

        if (!message) {
            orderFeedback.textContent = '';
            return;
        }

        let className = 'text-muted';
        if (tone === 'success') {
            className = 'text-success';
        } else if (tone === 'danger') {
            className = 'text-danger';
        } else if (tone === 'warning') {
            className = 'text-warning';
        }

        orderFeedback.classList.add(className);
        orderFeedback.textContent = message;

        if (tone === 'success') {
            orderFeedbackTimer = window.setTimeout(function () {
                orderFeedback.textContent = '';
                orderFeedback.classList.remove('text-success');
                orderFeedbackTimer = null;
            }, 2500);
        }
    }

    function getVisibleCount() {
        if (!list) {
            return state.items.length;
        }

        let count = 0;
        const items = list.querySelectorAll('[data-timeline-item-id]');
        items.forEach(function (item) {
            if (!item.classList.contains('d-none')) {
                count += 1;
            }
        });
        return count;
    }

    function submitOrder() {
        if (!orderForm || !orderInput) {
            return;
        }

        const formData = new FormData(orderForm);

        state.busy = true;
        setButtonLoading(saveButton, true);
        setOrderFeedback('Saving order...', 'muted');

        fetch(reorderUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
            .then(function (response) {
                return response.json()
                    .catch(function () {
                        throw buildTimelineError('Unexpected response received while saving order.', response.status);
                    })
                    .then(function (payload) {
                        if (!response.ok || !payload || payload.success === false) {
                            const message = payload && payload.message ? payload.message : 'Failed to update timeline order.';
                            throw buildTimelineError(message, response.status, payload);
                        }
                        return payload;
                    });
            })
            .then(function (payload) {
                const message = payload && payload.message ? payload.message : 'Timeline order updated successfully.';
                const ids = Array.isArray(payload && payload.order) ? payload.order : getCurrentOrderIds();
                if (ids.length) {
                    state.items = reorderItems(state.items, ids);
                }

                orderInput.defaultValue = orderInput.value;
                orderInput.dataset.sortableReference = orderInput.value;
                state.originalOrder = orderInput.value;

                if (saveButton) {
                    saveButton.disabled = true;
                }

                setOrderFeedback(message, 'success');
                showToast(message, 'success', true);
                renderPreview();
            })
            .catch(function (error) {
                const message = error && error.message ? error.message : 'Failed to update timeline order.';
                setOrderFeedback(message, 'danger');
                showToast(message, 'danger', false);
            })
            .finally(function () {
                state.busy = false;
                setButtonLoading(saveButton, false);
                updateButtonsDisabled();
            });
    }

    function buildTimelineError(message, status, payload) {
        const error = new Error(message);
        error.status = status;
        error.payload = payload;
        return error;
    }
}

function initializeHomePageCtaManager() {
    const container = document.querySelector('[data-home-cta]');
    if (!container) {
        return;
    }

    const defaults = {
        title: 'Ready to Start Your Project?',
        subtitle: "Let's partner to build inclusive, outcome-driven experiences.",
        primary_cta_text: 'Get In Touch',
        primary_cta_url: '/contact',
        secondary_cta_text: 'View Services',
        secondary_cta_url: '/services'
    };

    const form = container.querySelector('[data-home-cta-form]');
    const saveButton = container.querySelector('[data-home-cta-save]');
    const cancelButton = container.querySelector('[data-home-cta-cancel]');
    const errorsAlert = container.querySelector('[data-home-cta-errors]');
    const previewTitle = container.querySelector('[data-home-cta-preview="title"]');
    const previewSubtitle = container.querySelector('[data-home-cta-preview="subtitle"]');
    const previewPrimaryButton = container.querySelector('[data-home-cta-preview-primary]');
    const previewSecondaryButton = container.querySelector('[data-home-cta-preview-secondary]');
    const updateUrl = container.dataset.homeCtaUpdateUrl || '';

    const fields = {
        title: container.querySelector('[data-home-cta-field="title"]'),
        subtitle: container.querySelector('[data-home-cta-field="subtitle"]'),
        primary_cta_text: container.querySelector('[data-home-cta-field="primary_cta_text"]'),
        primary_cta_url: container.querySelector('[data-home-cta-field="primary_cta_url"]'),
        secondary_cta_text: container.querySelector('[data-home-cta-field="secondary_cta_text"]'),
        secondary_cta_url: container.querySelector('[data-home-cta-field="secondary_cta_url"]')
    };

    const routeSelects = {
        primary: container.querySelector('[data-home-cta-route-select="primary"]'),
        secondary: container.querySelector('[data-home-cta-route-select="secondary"]')
    };

    if (!form || !fields.title || !fields.primary_cta_text || !fields.primary_cta_url) {
        return;
    }

    let busy = false;
    let savedState = normalizeState(parseInitialState(), true);

    applyState(savedState);
    syncRouteSelects(savedState);
    renderPreview(savedState);
    updateButtons(savedState);

    Object.values(fields).forEach(function (field) {
        if (!field) {
            return;
        }
        field.addEventListener('input', refreshFromForm);
        field.addEventListener('change', refreshFromForm);
    });

    Object.entries(routeSelects).forEach(function ([key, select]) {
        if (!select) {
            return;
        }

        select.addEventListener('change', function () {
            const target = key === 'secondary' ? fields.secondary_cta_url : fields.primary_cta_url;
            if (!target) {
                refreshFromForm();
                return;
            }

            if (select.value === '__custom__') {
                target.value = '';
                target.focus();
                refreshFromForm();
                return;
            }

            if (target.value !== select.value) {
                target.value = select.value || '';
            }

            refreshFromForm();
        });
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        handleSave();
    });

    if (saveButton) {
        saveButton.addEventListener('click', handleSave);
        saveButton.dataset.originalHtml = saveButton.dataset.originalHtml || saveButton.innerHTML;
    }

    if (cancelButton) {
        cancelButton.addEventListener('click', function () {
            if (busy) {
                return;
            }

            applyState(savedState);
            syncRouteSelects(savedState);
            renderPreview(savedState);
            clearErrors();
            updateButtons(savedState);
        });
    }

    function parseInitialState() {
        const raw = container.dataset.homeCtaInitial || '';
        if (!raw) {
            return defaults;
        }

        try {
            const parsed = JSON.parse(raw);
            return parsed && typeof parsed === 'object' ? parsed : defaults;
        } catch (error) {
            console.warn('Failed to parse CTA initial state', error);
            return defaults;
        }
    }

    function refreshFromForm() {
        const current = collectState();
        syncRouteSelects(current);
        renderPreview(current);
        clearErrors();
        updateButtons(current);
    }

    function handleSave() {
        if (busy) {
            return;
        }

        if (!updateUrl) {
            showErrors(['Update URL is missing.']);
            return;
        }

        clearErrors();
        setBusy(true);

        const formData = new FormData(form);

        fetch(updateUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
            .then(function (response) {
                return response.json()
                    .catch(function () {
                        throw buildFetchError('Unexpected response format received from the server.', response.status);
                    })
                    .then(function (payload) {
                        if (!response.ok) {
                            throw buildFetchError(payload && payload.message ? payload.message : 'Failed to save homepage CTA.', response.status, payload);
                        }
                        return payload;
                    });
            })
            .then(function (payload) {
                if (!payload || !payload.success) {
                    throw buildFetchError(payload && payload.message ? payload.message : 'Failed to save homepage CTA.', 500, payload);
                }

                savedState = normalizeState(payload.cta || {}, false);
                applyState(savedState);
                syncRouteSelects(savedState);
                renderPreview(savedState);
                clearErrors();
                updateButtons(savedState);
                showToast(payload.message || 'Homepage CTA updated successfully.', 'success', true);
            })
            .catch(function (error) {
                const messages = extractErrorMessages(error);

                if (messages.length) {
                    showErrors(messages);
                    showToast(messages[0], 'danger', false);
                } else if (error && error.message) {
                    showErrors([error.message]);
                    showToast(error.message, 'danger', false);
                } else {
                    const fallback = 'Failed to save homepage CTA.';
                    showErrors([fallback]);
                    showToast(fallback, 'danger', false);
                }
            })
            .finally(function () {
                setBusy(false);
            });
    }

    function collectState() {
        const current = {
            title: sanitizeText(fields.title.value, 150),
            subtitle: sanitizeText(fields.subtitle.value, 240),
            primary_cta_text: sanitizeText(fields.primary_cta_text.value, 80),
            primary_cta_url: sanitizeUrl(fields.primary_cta_url.value),
            secondary_cta_text: sanitizeText(fields.secondary_cta_text ? fields.secondary_cta_text.value : '', 80),
            secondary_cta_url: sanitizeUrl(fields.secondary_cta_url ? fields.secondary_cta_url.value : '')
        };

        if (current.secondary_cta_text === '') {
            current.secondary_cta_url = '';
        }

        return current;
    }

    function sanitizeText(value, limit) {
        const trimmed = typeof value === 'string' ? value.trim() : '';
        if (typeof limit === 'number' && trimmed.length > limit) {
            return trimmed.slice(0, limit);
        }
        return trimmed;
    }

    function sanitizeUrl(value) {
        if (typeof value !== 'string') {
            return '';
        }

        let trimmed = value.trim();
        if (trimmed === '') {
            return '';
        }

        trimmed = trimmed.replace(/\s+/g, '');

        if (/^(https?:\/\/|mailto:|tel:)/i.test(trimmed)) {
            return trimmed.length > 255 ? trimmed.slice(0, 255) : trimmed;
        }

        if (trimmed.charAt(0) !== '/') {
            trimmed = '/' + trimmed.replace(/^\/+/, '');
        }

        return trimmed.length > 255 ? trimmed.slice(0, 255) : trimmed;
    }

    function normalizeState(source, useDefaults) {
        const base = source && typeof source === 'object' ? source : {};

        const normalized = {
            title: sanitizeText(base.title, 150),
            subtitle: sanitizeText(base.subtitle, 240),
            primary_cta_text: sanitizeText(base.primary_cta_text, 80),
            primary_cta_url: sanitizeUrl(base.primary_cta_url || ''),
            secondary_cta_text: sanitizeText(base.secondary_cta_text, 80),
            secondary_cta_url: sanitizeUrl(base.secondary_cta_url || '')
        };

        if (useDefaults) {
            if (normalized.title === '') {
                normalized.title = defaults.title;
            }
            if (normalized.primary_cta_text === '') {
                normalized.primary_cta_text = defaults.primary_cta_text;
            }
            if (normalized.primary_cta_url === '') {
                normalized.primary_cta_url = defaults.primary_cta_url;
            }
            if (normalized.secondary_cta_text === '') {
                normalized.secondary_cta_text = defaults.secondary_cta_text;
            }
            if (normalized.secondary_cta_url === '' && normalized.secondary_cta_text !== '') {
                normalized.secondary_cta_url = defaults.secondary_cta_url;
            }
        }

        if (normalized.secondary_cta_text === '') {
            normalized.secondary_cta_url = '';
        }

        return normalized;
    }

    function applyState(state) {
        fields.title.value = state.title || '';
        fields.subtitle.value = state.subtitle || '';
        fields.primary_cta_text.value = state.primary_cta_text || '';
        fields.primary_cta_url.value = state.primary_cta_url || '';

        if (fields.secondary_cta_text) {
            fields.secondary_cta_text.value = state.secondary_cta_text || '';
        }

        if (fields.secondary_cta_url) {
            fields.secondary_cta_url.value = state.secondary_cta_url || '';
        }
    }

    function renderPreview(state) {
        const titleText = state.title || defaults.title;
        const subtitleText = state.subtitle || '';
        const primaryText = state.primary_cta_text || defaults.primary_cta_text;
        const primaryUrl = state.primary_cta_url || '#';
        const secondaryText = state.secondary_cta_text;
        const secondaryUrl = state.secondary_cta_url;

        if (previewTitle) {
            previewTitle.textContent = titleText;
        }

        if (previewSubtitle) {
            previewSubtitle.textContent = subtitleText;
            previewSubtitle.classList.toggle('d-none', subtitleText === '');
        }

        if (previewPrimaryButton) {
            previewPrimaryButton.setAttribute('href', primaryUrl || '#');
            const textNode = previewPrimaryButton.querySelector('[data-home-cta-preview="primary_cta_text"]');
            if (textNode) {
                textNode.textContent = primaryText;
            }
        }

        if (previewSecondaryButton) {
            const showSecondary = secondaryText !== '' && secondaryUrl !== '';
            previewSecondaryButton.classList.toggle('d-none', !showSecondary);
            if (showSecondary) {
                previewSecondaryButton.setAttribute('href', secondaryUrl);
                const textNode = previewSecondaryButton.querySelector('[data-home-cta-preview="secondary_cta_text"]');
                if (textNode) {
                    textNode.textContent = secondaryText;
                }
            }
        }
    }

    function syncRouteSelects(state) {
        Object.entries(routeSelects).forEach(function ([key, select]) {
            if (!select) {
                return;
            }

            const url = key === 'secondary' ? state.secondary_cta_url : state.primary_cta_url;

            if (!url) {
                select.value = '';
                return;
            }

            const hasMatch = Array.from(select.options).some(function (option) {
                return option.value === url && option.value !== '__custom__';
            });

            select.value = hasMatch ? url : '__custom__';
        });
    }

    function updateButtons(currentState) {
        const dirty = !statesEqual(currentState, savedState);

        if (saveButton) {
            saveButton.disabled = busy || !dirty;
        }

        if (cancelButton) {
            cancelButton.disabled = busy || !dirty;
        }
    }

    function statesEqual(a, b) {
        return a.title === b.title &&
            a.subtitle === b.subtitle &&
            a.primary_cta_text === b.primary_cta_text &&
            a.primary_cta_url === b.primary_cta_url &&
            a.secondary_cta_text === b.secondary_cta_text &&
            a.secondary_cta_url === b.secondary_cta_url;
    }

    function clearErrors() {
        if (!errorsAlert) {
            return;
        }
        errorsAlert.classList.add('d-none');
        errorsAlert.replaceChildren();
    }

    function showErrors(messages) {
        if (!errorsAlert) {
            return;
        }

        errorsAlert.replaceChildren();

        if (!messages || !messages.length) {
            errorsAlert.classList.add('d-none');
            return;
        }

        messages.forEach(function (message) {
            const item = document.createElement('div');
            item.textContent = message;
            errorsAlert.appendChild(item);
        });

        errorsAlert.classList.remove('d-none');
    }

    function extractErrorMessages(error) {
        if (!error || !error.payload || !error.payload.errors) {
            return [];
        }

        if (Array.isArray(error.payload.errors)) {
            return error.payload.errors;
        }

        if (typeof error.payload.errors === 'object') {
            return Object.values(error.payload.errors).filter(function (value) {
                return typeof value === 'string' && value.trim() !== '';
            });
        }

        return [];
    }

    function setBusy(isBusy) {
        busy = isBusy;

        if (saveButton) {
            if (isBusy) {
                saveButton.disabled = true;
                saveButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...';
            } else if (saveButton.dataset.originalHtml) {
                saveButton.innerHTML = saveButton.dataset.originalHtml;
            }
        }

        if (cancelButton && isBusy) {
            cancelButton.disabled = true;
        }

        updateButtons(collectState());
    }

    function buildFetchError(message, status, payload) {
        const error = new Error(message);
        error.status = status;
        error.payload = payload;
        return error;
    }

    function showToast(message, tone, autoHide) {
        const text = typeof message === 'string' && message.trim() !== ''
            ? message.trim()
            : 'Action completed.';

        if (typeof bootstrap === 'undefined' || !bootstrap.Toast) {
            if (!autoHide) {
                window.alert(text);
            }
            return;
        }

        let containerEl = document.getElementById('adminToastContainer');
        if (!containerEl) {
            containerEl = document.createElement('div');
            containerEl.id = 'adminToastContainer';
            containerEl.className = 'toast-container position-fixed top-0 end-0 p-3';
            document.body.appendChild(containerEl);
        }

        const toastEl = document.createElement('div');
        toastEl.className = 'toast align-items-center text-bg-' + mapToneToBootstrap(tone) + ' border-0';
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');

        const toastWrapper = document.createElement('div');
        toastWrapper.className = 'd-flex';

        const toastBody = document.createElement('div');
        toastBody.className = 'toast-body';
        toastBody.textContent = text;

        const dismissButton = document.createElement('button');
        dismissButton.type = 'button';
        dismissButton.className = 'btn-close btn-close-white me-2 m-auto';
        dismissButton.setAttribute('data-bs-dismiss', 'toast');
        dismissButton.setAttribute('aria-label', 'Close');

        toastWrapper.appendChild(toastBody);
        toastWrapper.appendChild(dismissButton);
        toastEl.appendChild(toastWrapper);

        containerEl.appendChild(toastEl);

        const toast = new bootstrap.Toast(toastEl, {
            animation: true,
            autohide: autoHide !== false,
            delay: autoHide === false ? 0 : 2500
        });
        toast.show();

        toastEl.addEventListener('hidden.bs.toast', function () {
            toastEl.remove();
        });
    }

    function mapToneToBootstrap(tone) {
        switch (tone) {
            case 'success':
                return 'success';
            case 'danger':
                return 'danger';
            case 'warning':
                return 'warning';
            case 'info':
                return 'info';
            default:
                return 'secondary';
        }
    }
}

function initializeTestimonialsManager() {
    const manager = document.querySelector('[data-testimonials-manager]');
    if (!manager) {
        return;
    }

    const list = manager.querySelector('[data-testimonials-list]');
    const previewContainer = manager.querySelector('[data-testimonials-preview]');
    const previewEmpty = manager.querySelector('[data-testimonials-preview-empty]');
    const emptyState = manager.querySelector('[data-testimonials-empty]');
    const countBadge = manager.querySelector('[data-testimonials-count]');
    const feedback = manager.querySelector('[data-testimonials-feedback]');
    const saveButton = manager.querySelector('[data-testimonials-save]');
    const cancelButton = manager.querySelector('[data-testimonials-cancel]');
    const addButton = document.querySelector('[data-testimonials-add]');
    const draftIndicator = manager.querySelector('[data-testimonials-draft-indicator]');

    const listTemplate = document.getElementById('testimonialListItemTemplate');
    const previewTemplate = document.getElementById('testimonialPreviewTemplate');
    const modalElement = document.getElementById('testimonialModal');
    const deleteModalElement = document.getElementById('testimonialDeleteModal');

    if (!list || !listTemplate || !previewTemplate || !modalElement) {
        return;
    }

    const modal = bootstrap.Modal ? new bootstrap.Modal(modalElement) : null;
    const deleteModal = deleteModalElement && bootstrap.Modal ? new bootstrap.Modal(deleteModalElement) : null;

    const defaultImage = manager.dataset.testimonialsDefaultImage || '';
    const createUrl = manager.dataset.testimonialsCreateUrl || '';
    const updateBase = manager.dataset.testimonialsUpdateBase || '';
    const deleteBase = manager.dataset.testimonialsDeleteBase || '';
    const reorderUrl = manager.dataset.testimonialsReorderUrl || '';

    const initialData = parseTestimonials(manager.dataset.testimonialsInitial);

    const state = {
        testimonials: initialData,
        saved: initialData.map(cloneTestimonial),
        pendingDeleteId: null,
        pendingDeleteName: '',
        busy: false
    };

    const selectors = buildModalSelectors(modalElement);

    bindFormPreview(selectors, defaultImage);

    if (addButton) {
        addButton.addEventListener('click', function () {
            openModal();
        });
    }

    if (cancelButton) {
        cancelButton.addEventListener('click', function () {
            resetState();
        });
    }

    if (saveButton) {
        saveButton.addEventListener('click', handleSave);
    }

    if (deleteModalElement) {
        const confirmButton = deleteModalElement.querySelector('[data-testimonial-delete-confirm]');
        const nameTarget = deleteModalElement.querySelector('[data-testimonial-delete-name]');

        if (confirmButton) {
            confirmButton.addEventListener('click', function () {
                if (!state.pendingDeleteId) {
                    return;
                }
                deleteTestimonial(state.pendingDeleteId);
            });
        }

        deleteModalElement.addEventListener('show.bs.modal', function () {
            if (nameTarget) {
                nameTarget.textContent = state.pendingDeleteName || 'this client';
            }
        });
    }

    list.addEventListener('sortable:reordered', function () {
        synchronizeSortOrder();
        markDirty();
        renderPreview();
    });

    renderList();
    renderPreview();
    updateEmptyStates();
    updateCount();

    function parseTestimonials(raw) {
        if (!raw) {
            return [];
        }

        try {
            const parsed = JSON.parse(raw);
            if (Array.isArray(parsed)) {
                return parsed.map(normalizeTestimonial).filter(Boolean);
            }
        } catch (error) {
            console.warn('Failed to parse testimonials dataset', error);
        }

        return [];
    }

    function normalizeTestimonial(input) {
        if (!input || typeof input !== 'object') {
            return null;
        }

        const rawStatus = typeof input.status === 'string' ? input.status.toLowerCase() : null;
        const statusVisibility = rawStatus === 'published' ? 1 : (rawStatus === 'draft' ? 0 : null);
        let isVisible;

        if (statusVisibility !== null) {
            isVisible = statusVisibility;
        } else {
            const numericVisible = Number(input.is_visible);
            isVisible = Number.isFinite(numericVisible) && numericVisible === 1 ? 1 : 0;
        }

        const resolvedStatus = isVisible === 1 ? 'published' : 'draft';

        return {
            id: Number(input.id || 0),
            client_name: input.client_name || '',
            client_position: input.client_position || '',
            client_company: input.client_company || '',
            testimonial_text: input.testimonial_text || '',
            rating: clampRating(Number(input.rating || 5)),
            is_visible: isVisible,
            status: resolvedStatus,
            sort_order: Number(input.sort_order || 0),
            image_path: input.image_path || '',
            image_url: input.image_url || defaultImage,
            meta: input.meta || ''
        };
    }

    function cloneTestimonial(testimonial) {
        return Object.assign({}, testimonial);
    }

    function clampRating(value) {
        if (!Number.isFinite(value)) {
            return 5;
        }
        return Math.max(1, Math.min(5, Math.round(value)));
    }

    function buildModalSelectors(root) {
        return {
            form: root.querySelector('[data-testimonial-form]'),
            fields: {
                id: root.querySelector('[data-testimonial-field="id"]'),
                existingImage: root.querySelector('[data-testimonial-field="existing-image"]'),
                name: root.querySelector('input[name="client_name"]'),
                position: root.querySelector('input[name="client_position"]'),
                company: root.querySelector('input[name="client_company"]'),
                text: root.querySelector('textarea[name="testimonial_text"]'),
                rating: root.querySelector('select[name="rating"]'),
                visible: root.querySelector('input[name="display_toggle"]'),
                imageFile: root.querySelector('input[name="client_image_file"]'),
                removeImage: root.querySelector('input[name="remove_client_image"]')
            },
            preview: {
                name: root.querySelector('#testimonialModalPreviewName'),
                meta: root.querySelector('#testimonialModalPreviewMeta'),
                text: root.querySelector('#testimonialModalPreviewText'),
                stars: root.querySelector('#testimonialModalPreviewStars'),
                image: root.querySelector('#testimonialModalPreviewImage')
            },
            submit: root.querySelector('[data-testimonial-submit]')
        };
    }

    function bindFormPreview(refs, fallbackImage) {
        if (!refs.form) {
            return;
        }

        const form = refs.form;

        form.addEventListener('input', function () {
            refreshModalPreview(refs, fallbackImage);
        });

        form.addEventListener('change', function (event) {
            if (event.target && event.target === refs.fields.imageFile) {
                refreshModalPreview(refs, fallbackImage);
            }
        });

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            if (state.busy) {
                return;
            }

            submitForm(new FormData(form));
        });
    }

    function refreshModalPreview(refs, fallbackImage) {
        const name = refs.fields.name.value.trim() || 'Client Name';
        const position = refs.fields.position.value.trim();
        const company = refs.fields.company.value.trim();
        const text = refs.fields.text.value.trim() || 'Client feedback will appear here.';
        const rating = clampRating(Number(refs.fields.rating.value || 5));

        if (refs.preview.name) {
            refs.preview.name.textContent = name;
        }

        if (refs.preview.meta) {
            const combined = [position, company].filter(Boolean).join(' · ');
            refs.preview.meta.textContent = combined || 'Position · Company';
        }

        if (refs.preview.text) {
            refs.preview.text.textContent = text;
        }

        if (refs.preview.stars) {
            refs.preview.stars.innerHTML = renderStars(rating, true);
        }

        if (refs.preview.image) {
            const fileInput = refs.fields.imageFile;
            if (fileInput && fileInput.files && fileInput.files[0]) {
                const reader = new FileReader();
                reader.onload = function (loadEvent) {
                    const result = loadEvent.target && loadEvent.target.result ? loadEvent.target.result.toString() : fallbackImage;
                    refs.preview.image.src = result || fallbackImage;
                };
                reader.readAsDataURL(fileInput.files[0]);
            } else if (refs.fields.existingImage && refs.fields.existingImage.value) {
                refs.preview.image.src = refs.fields.existingImage.value;
            } else {
                refs.preview.image.src = fallbackImage;
            }
        }
    }

    function openModal(testimonial) {
        if (!modal) {
            return;
        }

        resetModalForm(testimonial);
        refreshModalPreview(selectors, defaultImage);
        modal.show();
    }

    function resetModalForm(testimonial) {
        const refs = selectors;
        if (!refs.form) {
            return;
        }

        refs.form.reset();
        refs.fields.imageFile.value = '';
        refs.fields.removeImage.checked = false;

        if (testimonial) {
            refs.fields.id.value = testimonial.id;
            refs.fields.existingImage.value = testimonial.image_url || testimonial.image_path || '';
            refs.fields.name.value = testimonial.client_name || '';
            refs.fields.position.value = testimonial.client_position || '';
            refs.fields.company.value = testimonial.client_company || '';
            refs.fields.text.value = testimonial.testimonial_text || '';
            refs.fields.rating.value = testimonial.rating || 5;
            refs.fields.visible.checked = testimonial.is_visible === 1;
            selectors.submit.innerHTML = '<i class="bi bi-check-lg me-2"></i>Save Changes';
            selectors.submit.disabled = false;
            modalElement.querySelector('.modal-title').textContent = 'Edit Testimonial';
        } else {
            refs.fields.id.value = '';
            refs.fields.existingImage.value = '';
            refs.fields.rating.value = 5;
            refs.fields.visible.checked = true;
            selectors.submit.innerHTML = '<i class="bi bi-check-lg me-2"></i>Save Testimonial';
            selectors.submit.disabled = false;
            modalElement.querySelector('.modal-title').textContent = 'Add Testimonial';
        }
    }

    function submitForm(formData) {
        state.busy = true;
        toggleModalBusy(true);

        const id = formData.get('testimonial_id');
        const targetUrl = id ? buildUpdateUrl(id) : createUrl;

        if (selectors && selectors.fields && selectors.fields.visible) {
            formData.set('display_toggle', selectors.fields.visible.checked ? '1' : '0');
        }

        fetch(targetUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
            .then(handleJsonResponse)
            .then(function (payload) {
                if (!payload || !payload.success) {
                    throw payload && payload.message ? new Error(payload.message) : new Error('Failed to save testimonial.');
                }

                modal.hide();
                addToast(payload.message || 'Testimonial saved.', 'success');

                const testimonial = normalizeTestimonial(payload.testimonial || {});
                if (!testimonial) {
                    return;
                }

                updateStateWithTestimonial(testimonial);
                markDirty();
                renderList();
                renderPreview();
                updateEmptyStates();
                updateCount();
            })
            .catch(function (error) {
                addToast(error.message || 'Failed to save testimonial.', 'danger', false);
            })
            .finally(function () {
                state.busy = false;
                toggleModalBusy(false);
            });
    }

    function buildUpdateUrl(id) {
        if (!updateBase) {
            return '';
        }

        return updateBase.replace(/\/$/, '') + '/' + encodeURIComponent(id) + '/edit';
    }

    function buildDeleteUrl(id) {
        if (!deleteBase) {
            return '';
        }

        return deleteBase.replace(/\/$/, '') + '/' + encodeURIComponent(id) + '/delete';
    }

    function toggleModalBusy(busy) {
        if (selectors.submit) {
            selectors.submit.disabled = busy;
        }
    }

    function updateStateWithTestimonial(testimonial) {
        const existingIndex = state.testimonials.findIndex(function (item) {
            return item.id === testimonial.id;
        });

        if (existingIndex >= 0) {
            state.testimonials.splice(existingIndex, 1, testimonial);
        } else {
            state.testimonials.push(testimonial);
        }

        state.testimonials.sort(function (a, b) {
            if (a.sort_order !== b.sort_order) {
                return a.sort_order - b.sort_order;
            }
            return a.id - b.id;
        });
    }

    function renderList() {
        list.innerHTML = '';

        state.testimonials.forEach(function (testimonial) {
            const fragment = listTemplate.content.cloneNode(true);
            const item = fragment.querySelector('[data-testimonial-item]');
            item.dataset.sortableId = String(testimonial.id || testimonial.temp_id || Date.now());
            item.dataset.testimonialId = String(testimonial.id);

            const thumb = fragment.querySelector('[data-testimonial-thumb]');
            const name = fragment.querySelector('[data-testimonial-name]');
            const meta = fragment.querySelector('[data-testimonial-meta]');
            const rating = fragment.querySelector('[data-testimonial-rating]');
            const snippet = fragment.querySelector('[data-testimonial-snippet]');
            const visibleInput = fragment.querySelector('[data-testimonial-visible]');
            const dirtyBadge = fragment.querySelector('[data-testimonial-dirty]');
            const editButton = fragment.querySelector('[data-testimonial-edit]');
            const deleteButton = fragment.querySelector('[data-testimonial-delete]');

            if (thumb) {
                thumb.src = testimonial.image_url || testimonial.image_path || defaultImage;
                thumb.alt = testimonial.client_name ? testimonial.client_name + ' avatar' : 'Client avatar';
            }

            if (name) {
                name.textContent = testimonial.client_name || 'Unnamed client';
            }

            if (meta) {
                meta.textContent = buildMeta(testimonial);
            }

            if (rating) {
                rating.innerHTML = renderStars(testimonial.rating);
            }

            if (snippet) {
                const text = testimonial.testimonial_text || '';
                snippet.textContent = text.length > 140 ? text.slice(0, 140) + '…' : text;
            }

            if (visibleInput) {
                visibleInput.checked = testimonial.is_visible === 1;
                visibleInput.addEventListener('change', function () {
                    testimonial.is_visible = visibleInput.checked ? 1 : 0;
                    testimonial.status = testimonial.is_visible === 1 ? 'published' : 'draft';
                    markDirty();
                    toggleDirtyBadge(true);
                    renderPreview();
                });
            }

            function toggleDirtyBadge(show) {
                if (!dirtyBadge) {
                    return;
                }
                dirtyBadge.classList.toggle('d-none', !show);
            }

            if (editButton) {
                editButton.addEventListener('click', function () {
                    openModal(testimonial);
                });
            }

            if (deleteButton) {
                deleteButton.addEventListener('click', function () {
                    state.pendingDeleteId = testimonial.id;
                    state.pendingDeleteName = testimonial.client_name || '';
                    if (deleteModal) {
                        deleteModal.show();
                    }
                });
            }

            list.appendChild(fragment);
        });
    }

    function renderPreview() {
        if (!previewContainer || !previewTemplate) {
            return;
        }

        previewContainer.innerHTML = '';

        const visibleTestimonials = state.testimonials
            .filter(function (testimonial) {
                return testimonial.is_visible === 1;
            })
            .sort(function (a, b) {
                if (a.sort_order !== b.sort_order) {
                    return a.sort_order - b.sort_order;
                }
                return a.id - b.id;
            });

        if (!visibleTestimonials.length) {
            if (previewEmpty) {
                previewEmpty.classList.remove('d-none');
            }
            if (draftIndicator) {
                draftIndicator.classList.add('d-none');
            }
            return;
        }

        if (previewEmpty) {
            previewEmpty.classList.add('d-none');
        }

        const hasDraft = !arraysEqual(visibleTestimonials, state.saved.filter(function (testimonial) {
            return testimonial.is_visible === 1;
        }));

        if (draftIndicator) {
            draftIndicator.classList.toggle('d-none', !hasDraft);
        }

        visibleTestimonials.forEach(function (testimonial) {
            const fragment = previewTemplate.content.cloneNode(true);
            const item = fragment.querySelector('[data-testimonial-preview-item]');
            const thumb = fragment.querySelector('[data-preview-thumb]');
            const stars = fragment.querySelector('[data-preview-stars]');
            const text = fragment.querySelector('[data-preview-text]');
            const name = fragment.querySelector('[data-preview-name]');
            const meta = fragment.querySelector('[data-preview-meta]');
            const draftBadge = fragment.querySelector('[data-preview-draft]');

            if (thumb) {
                thumb.src = testimonial.image_url || testimonial.image_path || defaultImage;
                thumb.alt = testimonial.client_name ? testimonial.client_name + ' avatar' : 'Client avatar';
            }

            if (stars) {
                stars.innerHTML = renderStars(testimonial.rating);
            }

            if (text) {
                text.textContent = testimonial.testimonial_text || '';
            }

            if (name) {
                name.textContent = testimonial.client_name || 'Client name';
            }

            if (meta) {
                const metaText = buildMeta(testimonial);
                meta.textContent = metaText;
                meta.classList.toggle('d-none', !metaText);
            }

            if (draftBadge) {
                const savedMatch = state.saved.find(function (item) {
                    return item.id === testimonial.id;
                });

                draftBadge.classList.toggle('d-none', savedMatch && savedMatch.is_visible === testimonial.is_visible);
            }

            previewContainer.appendChild(fragment);
        });
    }

    function buildMeta(testimonial) {
        const position = testimonial.client_position ? testimonial.client_position.trim() : '';
        const company = testimonial.client_company ? testimonial.client_company.trim() : '';
        if (!position && !company) {
            return '';
        }
        if (position && company) {
            return position + ' · ' + company;
        }
        return position || company;
    }

    function renderStars(rating, filledOnly) {
        const max = 5;
        let html = '';
        for (let i = 1; i <= max; i += 1) {
            if (i <= rating) {
                html += '<i class="bi bi-star-fill text-warning"></i>';
            } else if (!filledOnly) {
                html += '<i class="bi bi-star text-warning"></i>';
            }
        }
        return html;
    }

    function updateEmptyStates() {
        const hasTestimonials = state.testimonials.length > 0;
        if (emptyState) {
            emptyState.classList.toggle('d-none', hasTestimonials);
        }
    }

    function updateCount() {
        if (!countBadge) {
            return;
        }
        countBadge.textContent = state.testimonials.length + ' total';
    }

    function markDirty() {
        if (saveButton) {
            saveButton.disabled = false;
        }
        if (cancelButton) {
            cancelButton.disabled = false;
        }
    }

    function resetState() {
        state.testimonials = state.saved.map(cloneTestimonial);
        renderList();
        renderPreview();
        updateEmptyStates();
        updateCount();

        if (saveButton) {
            saveButton.disabled = true;
        }
        if (cancelButton) {
            cancelButton.disabled = true;
        }
        if (feedback) {
            feedback.textContent = '';
            feedback.classList.add('d-none');
        }
    }

    function synchronizeSortOrder() {
        const items = list.querySelectorAll('[data-testimonial-item]');
        items.forEach(function (item, index) {
            const id = Number(item.dataset.testimonialId || 0);
            const testimonial = state.testimonials.find(function (entry) {
                return entry.id === id;
            });
            if (testimonial) {
                testimonial.sort_order = index + 1;
            }
        });
    }

    function handleSave() {
        if (state.busy || !reorderUrl) {
            return;
        }

        state.busy = true;
        setFeedback('Saving changes…', 'info');
        saveButton.disabled = true;

        const payload = {
            items: state.testimonials.map(function (testimonial, index) {
                return {
                    id: testimonial.id,
                    sort_order: index + 1,
                    is_visible: testimonial.is_visible
                };
            })
        };

        fetch(reorderUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload)
        })
            .then(handleJsonResponse)
            .then(function (response) {
                if (!response || !response.success) {
                    throw new Error(response && response.message ? response.message : 'Failed to save testimonials.');
                }

                addToast(response.message || 'Testimonials saved.', 'success');
                state.testimonials = (response.testimonials || []).map(normalizeTestimonial);
                state.saved = state.testimonials.map(cloneTestimonial);
                renderList();
                renderPreview();
                updateEmptyStates();
                updateCount();

                saveButton.disabled = true;
                if (cancelButton) {
                    cancelButton.disabled = true;
                }
                setFeedback('Changes saved successfully.', 'success');
            })
            .catch(function (error) {
                addToast(error.message || 'Failed to save testimonials.', 'danger', false);
                saveButton.disabled = false;
                if (cancelButton) {
                    cancelButton.disabled = false;
                }
                setFeedback(error.message || 'Failed to save testimonials.', 'danger');
            })
            .finally(function () {
                state.busy = false;
            });
    }

    function deleteTestimonial(id) {
        if (state.busy) {
            return;
        }

        const url = buildDeleteUrl(id);
        if (!url) {
            return;
        }

        state.busy = true;

        fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
            .then(handleJsonResponse)
            .then(function (response) {
                if (!response || !response.success) {
                    throw new Error(response && response.message ? response.message : 'Failed to delete testimonial.');
                }

                addToast(response.message || 'Testimonial deleted.', 'success');
                state.testimonials = state.testimonials.filter(function (testimonial) {
                    return testimonial.id !== id;
                });
                state.saved = state.saved.filter(function (testimonial) {
                    return testimonial.id !== id;
                });
                renderList();
                renderPreview();
                updateEmptyStates();
                updateCount();
                markDirty();
            })
            .catch(function (error) {
                addToast(error.message || 'Failed to delete testimonial.', 'danger', false);
            })
            .finally(function () {
                state.busy = false;
                state.pendingDeleteId = null;
                state.pendingDeleteName = '';
                if (deleteModal) {
                    deleteModal.hide();
                }
            });
    }

    function handleJsonResponse(response) {
        if (!response.ok) {
            return response.json().catch(function () {
                throw new Error('Request failed with status ' + response.status);
            })
                .then(function (data) {
                    throw new Error(data && data.message ? data.message : 'Request failed.');
                });
        }

        return response.json().catch(function () {
            throw new Error('Unexpected response format received from the server.');
        });
    }

    function addToast(message, tone, autoHide = true) {
        if (typeof bootstrap === 'undefined' || !bootstrap.Toast) {
            if (autoHide) {
                setFeedback(message, tone);
            }
            return;
        }

        let container = document.getElementById('adminToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'adminToastContainer';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            document.body.appendChild(container);
        }

        const toastEl = document.createElement('div');
        toastEl.className = 'toast align-items-center text-bg-' + mapToneToBootstrap(tone) + ' border-0';
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');

        toastEl.innerHTML = '' +
            '<div class="d-flex">' +
            '  <div class="toast-body">' + message + '</div>' +
            '  <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>' +
            '</div>';

        container.appendChild(toastEl);

        const toast = new bootstrap.Toast(toastEl, {
            delay: autoHide ? 2500 : false
        });
        toast.show();

        if (autoHide) {
            toastEl.addEventListener('hidden.bs.toast', function () {
                toastEl.remove();
            });
        }
    }

    function mapToneToBootstrap(tone) {
        switch (tone) {
            case 'success':
                return 'success';
            case 'danger':
                return 'danger';
            case 'warning':
                return 'warning';
            case 'info':
                return 'info';
            default:
                return 'secondary';
        }
    }

    function setFeedback(message, tone) {
        if (!feedback) {
            return;
        }

        feedback.textContent = message || '';
        feedback.classList.remove('d-none', 'text-success', 'text-danger', 'text-warning', 'text-info');

        if (!message) {
            feedback.classList.add('d-none');
            return;
        }

        switch (tone) {
            case 'success':
                feedback.classList.add('text-success');
                break;
            case 'danger':
                feedback.classList.add('text-danger');
                break;
            case 'warning':
                feedback.classList.add('text-warning');
                break;
            case 'info':
                feedback.classList.add('text-info');
                break;
            default:
                feedback.classList.add('text-muted');
        }
    }

    function arraysEqual(left, right) {
        if (left.length !== right.length) {
            return false;
        }

        for (let index = 0; index < left.length; index += 1) {
            const a = left[index];
            const b = right[index];
            if (!a || !b || a.id !== b.id || a.is_visible !== b.is_visible || a.sort_order !== b.sort_order) {
                return false;
            }
        }

        return true;
    }
}

function setButtonLoading(button, isLoading) {
    if (!button) {
        return;
    }
    button.disabled = !!isLoading;
}

function renderListPreview(target, items, type, limit) {
    if (limit && items.length > limit) {
        items = items.slice(0, limit);
    }

    if (!items.length) {
        restoreTarget(target);
        return;
    }

    target.innerHTML = '';

    items.forEach(function (item) {
        switch (type) {
            case 'pill': {
                const span = document.createElement('span');
                span.className = 'badge rounded-pill bg-primary-subtle text-primary';
                span.textContent = item;
                target.appendChild(span);
                break;
            }
            case 'badge': {
                const span = document.createElement('span');
                span.className = 'badge bg-primary-subtle text-primary';
                span.textContent = item;
                target.appendChild(span);
                break;
            }
            default: {
                const li = document.createElement('li');
                li.textContent = item;
                target.appendChild(li);
                break;
            }
        }
    });
}

function applyToggle(target, reason, shouldHide, className) {
    className = className || 'd-none';
    let registry = toggleRegistry.get(target);
    if (!registry) {
        registry = { reasons: new Map(), className: className };
        toggleRegistry.set(target, registry);
    }

    if (registry.className !== className) {
        target.classList.remove(registry.className);
        registry.className = className;
    }

    if (shouldHide) {
        registry.reasons.set(reason, true);
    } else {
        registry.reasons.delete(reason);
    }

    if (registry.reasons.size > 0) {
        target.classList.add(registry.className);
    } else {
        target.classList.remove(registry.className);
    }
}

function getToggleReason(element) {
    if (!element) {
        return 'toggle';
    }
    if (element.dataset && element.dataset.previewTarget) {
        return `field:${element.dataset.previewTarget}`;
    }
    if (element.id) {
        return `element:#${element.id}`;
    }
    return 'toggle';
}

function escapeHtml(text) {
    return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function textToHtml(text) {
    return escapeHtml(text).replace(/\r?\n/g, '<br>');
}

function escapeSelector(value) {
    if (window.CSS && typeof window.CSS.escape === 'function') {
        return window.CSS.escape(value);
    }
    return value.replace(/([ #;?%&,.+*~':"!^$\[\]()=>|\/])/g, '\\$1');
}
