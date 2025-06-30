// Simple toast notification utility

interface ToastOptions {
  title: string;
  description?: string;
  variant?: 'default' | 'destructive';
}

export const toast = ({ title, description, variant = 'default' }: ToastOptions) => {
  // Create toast element
  const toastElement = document.createElement('div');
  toastElement.className = `
    fixed top-4 right-4 z-[9999] p-4 rounded-lg shadow-lg max-w-sm w-full transition-all duration-300 transform translate-x-0
    ${variant === 'destructive' 
      ? 'bg-red-600 text-white border border-red-600' 
      : 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100'
    }
  `;

  toastElement.style.cssText = `
    position: fixed;
    top: 1rem;
    right: 1rem;
    z-index: 9999;
    padding: 1rem;
    border-radius: 0.5rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    max-width: 24rem;
    width: 100%;
    transition: all 0.3s ease;
    transform: translateX(100%);
    ${variant === 'destructive' 
      ? 'background-color: #dc2626; color: white; border: 1px solid #dc2626;' 
      : 'background-color: white; color: #111827; border: 1px solid #e5e7eb;'
    }
  `;

  toastElement.innerHTML = `
    <div style="display: flex; align-items: flex-start; justify-content: space-between;">
      <div style="flex-grow: 1;">
        <div style="font-weight: 600; font-size: 0.875rem; margin-bottom: ${description ? '0.25rem' : '0'};">${title}</div>
        ${description ? `<div style="font-size: 0.875rem; opacity: 0.8;">${description}</div>` : ''}
      </div>
      <button style="
        margin-left: 0.75rem;
        flex-shrink: 0;
        background: none;
        border: none;
        cursor: pointer;
        opacity: 0.7;
        transition: opacity 0.2s ease;
        padding: 0;
        ${variant === 'destructive' ? 'color: rgba(255, 255, 255, 0.8);' : 'color: rgba(75, 85, 99, 0.8);'}
      " onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'" onclick="this.parentElement.parentElement.remove()">
        <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
  `;

  // Add to DOM
  document.body.appendChild(toastElement);

  // Animate in
  setTimeout(() => {
    toastElement.style.transform = 'translateX(0)';
  }, 10);

  // Auto remove after 5 seconds
  setTimeout(() => {
    if (toastElement.parentNode) {
      toastElement.style.transform = 'translateX(100%)';
      setTimeout(() => {
        if (toastElement.parentNode) {
          toastElement.remove();
        }
      }, 300);
    }
  }, 5000);
};
