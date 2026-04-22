import './bootstrap';
import ApexCharts from 'apexcharts';

window.ApexCharts = ApexCharts;

const THEME_KEY = 'theme';

function applySavedTheme() {
	const savedTheme = localStorage.getItem(THEME_KEY) || 'light';
	document.documentElement.setAttribute('data-theme', savedTheme);
}

function registerAlpineComponents() {
	if (!window.Alpine || window.__rosemaryModalRegistered) {
		return;
	}

	window.__rosemaryModalRegistered = true;

	window.Alpine.data('modal', (modalId, customEvents = []) => ({
		open: false,
		init() {
			const defaultEvents = [
				'close-create-modal',
				'close-edit-modal',
				'close-delete-modal',
				'close-detail-modal',
				'close-export-excel-modal',
				'close-export-pdf-modal',
			];

			const allEvents = [...defaultEvents, ...customEvents];

			allEvents.forEach((eventName) => {
				this.$wire.on(eventName, () => {
					this.closeModal();
				});
			});

			const modal = document.getElementById(modalId);
			modal?.addEventListener('close', () => {
				this.open = false;
			});
		},
		closeModal() {
			this.open = false;
			setTimeout(() => {
				document.getElementById(modalId)?.close();
			}, 300);
		},
		openModal() {
			this.open = true;
			this.$nextTick(() => {
				document.getElementById(modalId)?.showModal();
			});
		},
	}));
}

function highlightActiveMenu() {
	const currentPath = window.location.pathname;
	const menuLinks = document.querySelectorAll('.sidebar-menu a');

	menuLinks.forEach((link) => {
		const href = link.getAttribute('href');
		if (!href) {
			return;
		}

		const normalizedHref = href.replace(/\/$/, '');
		const isActive = href !== '/' && currentPath.startsWith(normalizedHref);
		link.parentElement?.classList.toggle('active', isActive);
	});
}

function enhanceNavigateLinks() {
	const navigateLinks = document.querySelectorAll('a[wire\\:navigate]:not([wire\\:navigate\\.hover])');

	navigateLinks.forEach((link) => {
		link.removeAttribute('wire:navigate');
		link.setAttribute('wire:navigate.hover', '');
	});
}

function bootstrapAppLayoutScripts() {
	registerAlpineComponents();
	highlightActiveMenu();
	enhanceNavigateLinks();
}

function destroyQuillInstances() {
	const hasQuillEditor =
		document.querySelector('.ql-toolbar, .ql-container, #quill-wrapper-create, #quill-wrapper-edit') !== null;

	if (!hasQuillEditor) {
		return;
	}

	window.quillCreateInstance = null;
	window.quillEditInstance = null;

	document.querySelectorAll('.ql-toolbar').forEach((el) => el.remove());

	document.querySelectorAll('.ql-container').forEach((el) => {
		el.classList.remove('ql-container', 'ql-snow', 'ql-blank');
		el.innerHTML = '';
	});

	const wrapperCreate = document.getElementById('quill-wrapper-create');
	if (wrapperCreate) {
		wrapperCreate.innerHTML = '<div id="quill-editor-create" style="height: 300px;"></div>';
	}

	const wrapperEdit = document.getElementById('quill-wrapper-edit');
	if (wrapperEdit) {
		wrapperEdit.innerHTML = '<div id="quill-editor-edit" style="height: 300px;"></div>';
	}
}

function initPullToRefresh() {
	if (window.__rosemaryPullToRefreshInitialized) {
		return;
	}

	window.__rosemaryPullToRefreshInitialized = true;

	let startY = 0;
	let currentY = 0;
	let isPulling = false;
	let isRefreshing = false;
	let scrollAttempts = 0;
	let scrollTimer = null;
	const threshold = 80;

	const getPullIndicator = () => document.getElementById('pull-to-refresh');

	function showIndicator() {
		const indicator = getPullIndicator();
		if (!indicator) {
			return;
		}

		indicator.classList.remove('opacity-0', '-translate-y-full');
		indicator.classList.add('opacity-100', 'translate-y-0');
	}

	function hideIndicator() {
		const indicator = getPullIndicator();
		if (!indicator) {
			return;
		}

		indicator.classList.remove('opacity-100', 'translate-y-0');
		indicator.classList.add('opacity-0', '-translate-y-full');
		indicator.style.removeProperty('transform');
		indicator.style.removeProperty('opacity');
	}

	function refreshData() {
		if (isRefreshing) {
			return;
		}

		isRefreshing = true;
		showIndicator();

		try {
			if (window.Livewire) {
				document.querySelectorAll('[wire\\:id]').forEach((element) => {
					const componentId = element.getAttribute('wire:id');
					if (!componentId) {
						return;
					}

					const component = window.Livewire.find(componentId);
					if (component && typeof component.$refresh === 'function') {
						component.$refresh();
					}
				});
			}
		} catch (error) {
			console.error('Refresh error:', error);
		}

		setTimeout(() => {
			isRefreshing = false;
			hideIndicator();
		}, 900);
	}

	document.addEventListener(
		'touchstart',
		(event) => {
			if (window.scrollY === 0 && !isRefreshing) {
				startY = event.touches[0].pageY;
				isPulling = true;
			}
		},
		{ passive: true }
	);

	document.addEventListener(
		'touchmove',
		(event) => {
			if (!isPulling || isRefreshing) {
				return;
			}

			currentY = event.touches[0].pageY;
			const pullDistance = currentY - startY;

			if (pullDistance > 0 && pullDistance < threshold && window.scrollY === 0) {
				const progress = Math.min(pullDistance / threshold, 1);
				const indicator = getPullIndicator();
				if (!indicator) {
					return;
				}

				indicator.style.transform = `translateY(${progress * 100 - 100}%)`;
				indicator.style.opacity = String(progress);
			}
		},
		{ passive: true }
	);

	document.addEventListener(
		'touchend',
		() => {
			if (!isPulling) {
				return;
			}

			const pullDistance = currentY - startY;

			if (pullDistance >= threshold && window.scrollY === 0 && !isRefreshing) {
				refreshData();
			} else {
				hideIndicator();
			}

			isPulling = false;
			startY = 0;
			currentY = 0;
		},
		{ passive: true }
	);

	document.addEventListener(
		'wheel',
		(event) => {
			if (event.deltaY < 0 && window.scrollY === 0 && !isRefreshing) {
				scrollAttempts += 1;

				clearTimeout(scrollTimer);
				scrollTimer = setTimeout(() => {
					scrollAttempts = 0;
				}, 500);

				if (scrollAttempts >= 3) {
					refreshData();
					scrollAttempts = 0;
				}
			}
		},
		{ passive: true }
	);

	document.addEventListener('keydown', (event) => {
		if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'r' && !isRefreshing) {
			event.preventDefault();
			refreshData();
		}
	});
}

window.toggleSidebar = function toggleSidebar() {
	const drawer = document.getElementById('sidebar-drawer');
	if (drawer) {
		drawer.checked = !drawer.checked;
	}
};

applySavedTheme();
initPullToRefresh();

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', bootstrapAppLayoutScripts, { once: true });
} else {
	bootstrapAppLayoutScripts();
}

document.addEventListener('alpine:init', registerAlpineComponents);
document.addEventListener('livewire:initialized', bootstrapAppLayoutScripts);

document.addEventListener('livewire:navigating', () => {
	destroyQuillInstances();
});

document.addEventListener('livewire:navigated', () => {
	applySavedTheme();
	bootstrapAppLayoutScripts();
});
