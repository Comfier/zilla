import { authAPI, ordersAPI } from './api.js';

let currentUser = null;
let currentOrder = null;

// Check authentication and load order details
async function initPayment() {
    try {
        // Check auth
        const authResult = await authAPI.check();
        if (!authResult.authenticated || authResult.user.role !== 'client') {
            window.location.href = 'client-login.html';
            return;
        }
        
        currentUser = authResult.user;
        
        // Get order ID from URL
        const urlParams = new URLSearchParams(window.location.search);
        const orderId = urlParams.get('order');
        
        if (!orderId) {
            alert('No order ID provided. Redirecting to orders page...');
            window.location.href = 'client-orders.html';
            return;
        }
        
        // Load order details
        await loadOrderDetails(orderId);
        
        // Setup payment method selection
        setupPaymentMethods();
        
        // Setup form submission
        setupPaymentForm(orderId);
        
    } catch (error) {
        console.error('Payment init error:', error);
        alert('Error loading payment page. Redirecting...');
        window.location.href = 'client-orders.html';
    }
}

// Load order details
async function loadOrderDetails(orderId) {
    try {
        const orders = await ordersAPI.getAll();
        const order = orders.find(o => o.id == orderId);
        
        if (!order) {
            alert('Order not found. Redirecting...');
            window.location.href = 'client-orders.html';
            return;
        }
        
        // Note: Orders are already filtered by user_id in the API, so this check is redundant
        // but kept for extra security
        
        currentOrder = order;
        
        // Display order details
        document.getElementById('orderIdDisplay').textContent = order.id;
        document.getElementById('orderDesign').textContent = order.design || '-';
        document.getElementById('orderMeasurements').textContent = order.measurements || '-';
        
        // Calculate amount (simulated - in real system, this would come from furniture pricing)
        const amount = calculateOrderAmount(order);
        document.getElementById('orderSubtotal').textContent = formatCurrency(amount);
        document.getElementById('orderTotal').textContent = formatCurrency(amount);
        
        // Load furniture name if available
        if (order.furnitureId) {
            try {
                const { furnitureAPI } = await import('./api.js');
                const furniture = await furnitureAPI.getById(order.furnitureId);
                if (furniture) {
                    document.getElementById('orderItemName').textContent = furniture.name;
                }
            } catch (error) {
                console.error('Error loading furniture:', error);
                document.getElementById('orderItemName').textContent = 'Custom Order';
            }
        } else {
            document.getElementById('orderItemName').textContent = 'Custom Order';
        }
        
    } catch (error) {
        console.error('Error loading order:', error);
        alert('Error loading order details.');
    }
}

// Calculate order amount (simulated pricing)
function calculateOrderAmount(order) {
    // In a real system, this would calculate based on furniture price, measurements, etc.
    // For demo, we'll use a base price with some variation
    const basePrice = 500;
    const measurements = order.measurements || '';
    
    // Simple calculation based on measurements text length (demo only)
    let price = basePrice;
    if (measurements.length > 100) {
        price += 200; // Larger items cost more
    }
    if (order.instructions && order.instructions.length > 50) {
        price += 100; // Complex instructions add cost
    }
    
    // Add some randomness for demo
    price += Math.floor(Math.random() * 300);
    
    return price;
}

// Format currency
function formatCurrency(amount) {
    return '$' + amount.toFixed(2);
}

// Setup payment method selection
function setupPaymentMethods() {
    const paymentMethods = document.querySelectorAll('input[name="paymentMethod"]');
    const cardDetails = document.getElementById('cardDetails');
    
    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            // Update visual selection
            document.querySelectorAll('.payment-method').forEach(pm => {
                pm.classList.remove('selected');
            });
            this.closest('.payment-method').classList.add('selected');
            
            // Show/hide card details
            if (this.value === 'card') {
                cardDetails.classList.add('show');
                // Make card fields required
                document.getElementById('cardNumber').required = true;
                document.getElementById('cardExpiry').required = true;
                document.getElementById('cardCVC').required = true;
                document.getElementById('cardName').required = true;
            } else {
                cardDetails.classList.remove('show');
                // Make card fields optional
                document.getElementById('cardNumber').required = false;
                document.getElementById('cardExpiry').required = false;
                document.getElementById('cardCVC').required = false;
                document.getElementById('cardName').required = false;
            }
        });
    });
    
    // Initialize first method as selected
    if (paymentMethods.length > 0) {
        paymentMethods[0].closest('.payment-method').classList.add('selected');
    }
}

// Setup payment form submission
function setupPaymentForm(orderId) {
    const paymentForm = document.getElementById('paymentForm');
    const payButton = document.getElementById('payButton');
    const payButtonText = document.getElementById('payButtonText');
    const payButtonLoader = document.getElementById('payButtonLoader');
    const paymentError = document.getElementById('paymentError');
    const paymentSuccess = document.getElementById('paymentSuccess');
    const paymentLoading = document.getElementById('paymentLoading');
    
    paymentForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        paymentError.textContent = '';
        paymentError.classList.remove('show');
        paymentSuccess.classList.remove('show');
        
        const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
        
        // Validate card details if card payment
        if (paymentMethod === 'card') {
            const cardNumber = document.getElementById('cardNumber').value.replace(/\s/g, '');
            const cardExpiry = document.getElementById('cardExpiry').value;
            const cardCVC = document.getElementById('cardCVC').value;
            const cardName = document.getElementById('cardName').value;
            
            if (!cardNumber || cardNumber.length < 13) {
                paymentError.textContent = 'Please enter a valid card number.';
                paymentError.classList.add('show');
                return;
            }
            
            if (!cardExpiry || !/^\d{2}\/\d{2}$/.test(cardExpiry)) {
                paymentError.textContent = 'Please enter a valid expiry date (MM/YY).';
                paymentError.classList.add('show');
                return;
            }
            
            if (!cardCVC || cardCVC.length < 3) {
                paymentError.textContent = 'Please enter a valid CVC.';
                paymentError.classList.add('show');
                return;
            }
            
            if (!cardName) {
                paymentError.textContent = 'Please enter cardholder name.';
                paymentError.classList.add('show');
                return;
            }
        }
        
        // Show loading
        payButton.disabled = true;
        payButtonText.style.display = 'none';
        payButtonLoader.style.display = 'inline';
        paymentLoading.classList.add('show');
        
        try {
            // Calculate amount
            const amount = calculateOrderAmount(currentOrder);
            
            // Process payment
            const paymentData = {
                orderId: parseInt(orderId),
                paymentMethod: paymentMethod,
                amount: amount,
                cardNumber: paymentMethod === 'card' ? document.getElementById('cardNumber').value.replace(/\s/g, '') : null,
                cardExpiry: paymentMethod === 'card' ? document.getElementById('cardExpiry').value : null,
                cardCVC: paymentMethod === 'card' ? document.getElementById('cardCVC').value : null,
                cardName: paymentMethod === 'card' ? document.getElementById('cardName').value : null
            };
            
            // Simulate payment processing delay
            await new Promise(resolve => setTimeout(resolve, 2000));
            
            // Call payment API
            const result = await processPayment(paymentData);
            
            if (result.success) {
                paymentSuccess.textContent = 'Payment successful! Redirecting to orders...';
                paymentSuccess.classList.add('show');
                
                setTimeout(() => {
                    window.location.href = 'client-orders.html';
                }, 2000);
            } else {
                throw new Error(result.error || 'Payment failed');
            }
            
        } catch (error) {
            console.error('Payment error:', error);
            paymentError.textContent = error.message || 'Payment failed. Please try again.';
            paymentError.classList.add('show');
            paymentLoading.classList.remove('show');
        } finally {
            payButton.disabled = false;
            payButtonText.style.display = 'inline';
            payButtonLoader.style.display = 'none';
        }
    });
}

// Process payment via API
async function processPayment(paymentData) {
    const response = await fetch('php/payment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        credentials: 'include',
        body: JSON.stringify(paymentData)
    });
    
    const result = await response.json();
    
    if (!response.ok) {
        throw new Error(result.error || 'Payment processing failed');
    }
    
    return result;
}

// Format card number input
const cardNumberInput = document.getElementById('cardNumber');
if (cardNumberInput) {
    cardNumberInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\s/g, '');
        let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
        e.target.value = formattedValue;
    });
}

// Format expiry date input
const cardExpiryInput = document.getElementById('cardExpiry');
if (cardExpiryInput) {
    cardExpiryInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        e.target.value = value;
    });
}

// Initialize on page load
initPayment();

