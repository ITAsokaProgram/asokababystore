// Animation Handler for Member Management
import { el } from "../services/dom.js";

class AnimationHandler {
    constructor() {
        this.init();
    }

    init() {
        this.bindCounterAnimations();
        this.bindChartAnimations();
        this.bindElementAnimations();
    }

    bindCounterAnimations() {
        // Animated counters
        this.animateCounters();
    }

    bindChartAnimations() {
        // Animate chart elements if present
        this.animateCharts();
    }

    bindElementAnimations() {
        // Fade in animations for elements
        this.fadeInElements();
    }

    animateCounters() {
        const counters = document.querySelectorAll('.animate-counter');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.startCounterAnimation(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        });

        counters.forEach(counter => observer.observe(counter));
    }

    startCounterAnimation(element) {
        const target = parseInt(element.getAttribute('data-target')) || 0;
        const duration = 2000; // 2 seconds
        const increment = target / (duration / 16); // ~60fps
        let current = 0;

        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                element.textContent = target.toLocaleString();
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(current).toLocaleString();
            }
        }, 16);
    }

    animateCharts() {
        if (typeof gsap !== 'undefined') {
            // Progress bars animation
            gsap.from(".progress-bar", {
                width: 0,
                duration: 1.5,
                ease: "power2.out",
                stagger: 0.2
            });

            // Chart elements animation
            gsap.from(".chart-element", {
                scale: 0,
                duration: 1,
                ease: "back.out(1.7)",
                stagger: 0.1
            });
        }
    }

    fadeInElements() {
        if (typeof gsap !== 'undefined') {
            // Card animations
            gsap.from(".stats-card", {
                y: 30,
                opacity: 0,
                duration: 0.8,
                ease: "power2.out",
                stagger: 0.1
            });

            // Table rows animation
            gsap.from(".table-row", {
                x: -20,
                opacity: 0,
                duration: 0.6,
                ease: "power2.out",
                stagger: 0.05
            });

            // Button animations
            gsap.from(".animated-button", {
                scale: 0.8,
                opacity: 0,
                duration: 0.5,
                ease: "back.out(1.7)",
                stagger: 0.1
            });
        }
    }

    // Specific animations for modals
    animateModalOpen(modalElement) {
        if (typeof gsap !== 'undefined') {
            gsap.fromTo(modalElement, 
                { 
                    opacity: 0,
                    scale: 0.9 
                },
                { 
                    opacity: 1,
                    scale: 1,
                    duration: 0.3,
                    ease: "power2.out"
                }
            );
        }
    }

    animateModalClose(modalElement) {
        if (typeof gsap !== 'undefined') {
            return gsap.to(modalElement, {
                opacity: 0,
                scale: 0.9,
                duration: 0.2,
                ease: "power2.in"
            });
        }
        return Promise.resolve();
    }

    // Loading animations
    showLoadingAnimation(element) {
        if (typeof gsap !== 'undefined') {
            element.innerHTML = `
                <div class="flex items-center justify-center space-x-2">
                    <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce"></div>
                    <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                    <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                </div>
            `;
        }
    }

    hideLoadingAnimation(element, originalContent) {
        element.innerHTML = originalContent;
    }

    // Success/Error animations
    showSuccessAnimation(element) {
        if (typeof gsap !== 'undefined') {
            gsap.fromTo(element,
                { scale: 0.8, opacity: 0 },
                { 
                    scale: 1, 
                    opacity: 1, 
                    duration: 0.5,
                    ease: "back.out(1.7)" 
                }
            );
        }
    }

    showErrorAnimation(element) {
        if (typeof gsap !== 'undefined') {
            gsap.fromTo(element,
                { x: -10 },
                { 
                    x: 0, 
                    duration: 0.1,
                    repeat: 5,
                    yoyo: true,
                    ease: "power2.inOut"
                }
            );
        }
    }

    // Hover animations for interactive elements
    addHoverAnimations() {
        document.querySelectorAll('.hover-animate').forEach(element => {
            element.addEventListener('mouseenter', () => {
                if (typeof gsap !== 'undefined') {
                    gsap.to(element, {
                        scale: 1.05,
                        duration: 0.2,
                        ease: "power2.out"
                    });
                }
            });

            element.addEventListener('mouseleave', () => {
                if (typeof gsap !== 'undefined') {
                    gsap.to(element, {
                        scale: 1,
                        duration: 0.2,
                        ease: "power2.out"
                    });
                }
            });
        });
    }

    // Slide animations for panels
    slideInPanel(panel, direction = 'right') {
        if (typeof gsap !== 'undefined') {
            const startX = direction === 'right' ? '100%' : '-100%';
            
            gsap.fromTo(panel,
                { x: startX, opacity: 0 },
                { 
                    x: '0%', 
                    opacity: 1,
                    duration: 0.4,
                    ease: "power2.out"
                }
            );
        }
    }

    slideOutPanel(panel, direction = 'right') {
        if (typeof gsap !== 'undefined') {
            const endX = direction === 'right' ? '100%' : '-100%';
            
            return gsap.to(panel, {
                x: endX,
                opacity: 0,
                duration: 0.3,
                ease: "power2.in"
            });
        }
        return Promise.resolve();
    }

    // Refresh table animation
    refreshTableAnimation() {
        if (typeof gsap !== 'undefined') {
            const tableRows = document.querySelectorAll('.table-row');
            
            gsap.fromTo(tableRows,
                { opacity: 0, y: 20 },
                {
                    opacity: 1,
                    y: 0,
                    duration: 0.4,
                    stagger: 0.05,
                    ease: "power2.out"
                }
            );
        }
    }

    // Notification animations
    showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
            type === 'success' ? 'bg-green-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
            type === 'warning' ? 'bg-yellow-500 text-white' :
            'bg-blue-500 text-white'
        }`;
        notification.innerHTML = `
            <div class="flex items-center space-x-2">
                <i class="fas ${
                    type === 'success' ? 'fa-check-circle' :
                    type === 'error' ? 'fa-exclamation-circle' :
                    type === 'warning' ? 'fa-exclamation-triangle' :
                    'fa-info-circle'
                }"></i>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(notification);

        if (typeof gsap !== 'undefined') {
            // Animate in
            gsap.fromTo(notification,
                { x: '100%', opacity: 0 },
                { 
                    x: '0%', 
                    opacity: 1,
                    duration: 0.4,
                    ease: "power2.out"
                }
            );

            // Auto remove after 3 seconds
            gsap.to(notification, {
                x: '100%',
                opacity: 0,
                duration: 0.3,
                ease: "power2.in",
                delay: 3,
                onComplete: () => {
                    document.body.removeChild(notification);
                }
            });
        } else {
            // Fallback without GSAP
            setTimeout(() => {
                if (notification.parentNode) {
                    document.body.removeChild(notification);
                }
            }, 3000);
        }
    }
}

// Export instance
export const animationHandler = new AnimationHandler();
