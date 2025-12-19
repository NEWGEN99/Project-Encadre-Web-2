
// --- 1. DATA SIMULATION (Database & State) ---

// Default products if database (localStorage) is empty
const defaultProducts = [
    {
        id: 1,
        name: 'NVIDIA RTX 4090 Founder Edition',
        price: 1599.99,
        description: 'The ultimate GeForce GPU. A huge leap in performance.',
        category: 'Graphics Cards',
        imageUrl: 'https://images.unsplash.com/photo-1694558276685-6490332d4b96?auto=format&fit=crop&q=80&w=600',
        isNew: true
    },
    {
        id: 2,
        name: 'AMD Ryzen 9 7950X3D',
        price: 699.00,
        description: 'The dominant gaming processor with AMD 3D V-Cache.',
        category: 'Processors',
        imageUrl: 'https://images.unsplash.com/photo-1591799264318-7e6ef8ddb7ea?auto=format&fit=crop&q=80&w=600',
        isNew: true
    },
    {
        id: 3,
        name: 'Samsung 990 PRO 2TB NVMe',
        price: 169.99,
        originalPrice: 249.99,
        description: 'Blazing fast speeds for gaming and creative work.',
        category: 'Storage',
        imageUrl: 'https://images.unsplash.com/photo-1628557044797-f21a177c37ec?auto=format&fit=crop&q=80&w=600',
        isSale: true
    },
    {
        id: 4,
        name: 'Corsair Dominator Platinum 32GB',
        price: 189.99,
        description: 'Push the limits of performance with DDR5 memory.',
        category: 'Memory',
        imageUrl: 'https://images.unsplash.com/photo-1562976540-1502c2145186?auto=format&fit=crop&q=80&w=600'
    },
    {
        id: 5,
        name: 'Logitech G Pro X Superlight',
        price: 109.99,
        originalPrice: 159.99,
        description: 'Meticulously designed esports wireless mouse.',
        category: 'Peripherals',
        imageUrl: 'https://images.unsplash.com/photo-1615663245857-acda5b2b8856?auto=format&fit=crop&q=80&w=600',
        isSale: true
    },
    {
        id: 6,
        name: 'ASUS ROG Maximus Z790 Hero',
        price: 629.99,
        description: 'Intel Z790 ATX motherboard with 20+1 power stages.',
        category: 'Motherboards',
        imageUrl: 'https://images.unsplash.com/photo-1555680202-c86f0e12f086?auto=format&fit=crop&q=80&w=600'
    },
     {
        id: 7,
        name: 'Lian Li O11 Dynamic Evo',
        price: 169.99,
        description: 'Mid-tower chassis with two modes: Normal and Reverse.',
        category: 'Cases',
        imageUrl: 'https://images.unsplash.com/photo-1587202372634-32705e3bf49c?auto=format&fit=crop&q=80&w=600'
    },
    {
        id: 8,
        name: 'NZXT Kraken Elite 360 RGB',
        price: 299.99,
        description: 'High-performance liquid cooling with LCD display.',
        category: 'Cooling',
        imageUrl: 'https://images.unsplash.com/photo-1587202372775-e229f172b9d7?auto=format&fit=crop&q=80&w=600'
    },
];

// Initialize State
let products = JSON.parse(localStorage.getItem('nexus_products')) || defaultProducts;
let cart = JSON.parse(localStorage.getItem('nexus_cart')) || [];
let isAdminAuthenticated = sessionStorage.getItem('nexus_admin_auth') === 'true';
let heroInterval;

// --- 2. CORE FUNCTIONS & HELPERS ---

function saveProducts() {
    localStorage.setItem('nexus_products', JSON.stringify(products));
}

function saveCart() {
    localStorage.setItem('nexus_cart', JSON.stringify(cart));
    updateCartBadge();
}

function formatPrice(price) {
    return '$' + parseFloat(price).toFixed(2);
}

function refreshIcons() {
    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }
}

function updateCartBadge() {
    const count = cart.reduce((sum, item) => sum + item.quantity, 0);
    const badge = document.getElementById('cart-badge');
    if (badge) {
        if (count > 0) {
            badge.innerText = count;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }
}

function toggleMobileMenu() {
    const menu = document.getElementById('mobile-menu');
    menu.classList.toggle('hidden');
}

// Global actions exposed to HTML
window.addToCart = function(productId) {
    const product = products.find(p => p.id === productId);
    if (!product) return;

    const existingItem = cart.find(item => item.id === productId);
    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.push({ ...product, quantity: 1 });
    }
    saveCart();
    
    // Visual feedback
    const btn = document.querySelector(`button[onclick="addToCart(${productId})"]`);
    if (btn) {
        const originalContent = btn.innerHTML;
        const originalClasses = btn.className;
        
        btn.innerHTML = `<i data-lucide="check" class="w-4 h-4"></i> Added`;
        btn.className = "bg-green-500 text-white w-full py-2 rounded-lg font-bold flex items-center justify-center gap-2 transition-all";
        refreshIcons();
        
        setTimeout(() => {
            btn.innerHTML = originalContent;
            btn.className = originalClasses;
            refreshIcons();
        }, 1500);
    }
};

window.removeFromCart = function(productId) {
    cart = cart.filter(item => item.id !== productId);
    saveCart();
    renderCartPage();
};

window.updateCartQuantity = function(productId, change) {
    const item = cart.find(i => i.id === productId);
    if (item) {
        item.quantity += change;
        if (item.quantity <= 0) {
            window.removeFromCart(productId);
        } else {
            saveCart();
            renderCartPage();
        }
    }
};

// --- 3. COMPONENTS ---

function createProductCard(product) {
    const isSale = product.originalPrice ? true : false;
    return `
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden hover:shadow-xl hover:shadow-yellow-400/10 transition-all duration-300 group flex flex-col h-full card-hover">
            <div class="relative aspect-[4/3] overflow-hidden bg-gray-100">
                <img src="${product.imageUrl}" alt="${product.name}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                ${isSale ? `<span class="absolute top-3 left-3 bg-rose-500 text-white text-[10px] font-bold px-2 py-1 rounded-full uppercase tracking-wider">Sale</span>` : ''}
                ${product.isNew ? `<span class="absolute top-3 left-3 bg-yellow-400 text-gray-900 text-[10px] font-bold px-2 py-1 rounded-full uppercase tracking-wider">New</span>` : ''}
            </div>
            <div class="p-5 flex flex-col flex-1">
                <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-1">${product.category}</div>
                <h3 class="font-bold text-gray-900 leading-tight mb-2 group-hover:text-yellow-600 transition-colors">${product.name}</h3>
                <p class="text-gray-500 text-xs mb-4 line-clamp-2 flex-1">${product.description}</p>
                <div class="flex items-center justify-between mt-auto gap-4">
                    <div class="flex flex-col">
                        ${isSale ? `<span class="text-xs text-gray-400 line-through font-medium">${formatPrice(product.originalPrice)}</span>` : ''}
                        <span class="text-lg font-bold text-gray-900">${formatPrice(product.price)}</span>
                    </div>
                    <button onclick="addToCart(${product.id})" class="bg-gray-900 hover:bg-yellow-400 hover:text-gray-900 text-white w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-300 shadow-lg shadow-gray-900/20 active:scale-95" aria-label="Add to cart">
                        <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
}

// --- 4. PAGE RENDERERS ---

function renderHomePage() {
    if (heroInterval) clearInterval(heroInterval);
    const container = document.getElementById('app-content');
    
    // Featured products
    const featured = products.slice(0, 4);

    const html = `
        <!-- Hero Slider -->
        <div id="hero-container" class="relative mb-16 rounded-3xl overflow-hidden min-h-[400px] md:min-h-[500px] shadow-2xl group border border-gray-900 bg-gray-900">
            <!-- Content injected by startHeroSlider -->
        </div>

        <!-- Categories Grid -->
        <div class="mb-20 fade-in">
             <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Browse Categories</h2>
                <a href="#products" class="text-sm font-semibold text-yellow-600 hover:text-yellow-700 flex items-center gap-1">View All <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
             </div>
             <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                ${['Graphics Cards', 'Processors', 'Storage', 'Peripherals'].map(cat => `
                    <div onclick="window.location.hash='#products'; setTimeout(()=>filterProducts('${cat}'), 100)" class="cursor-pointer group bg-white border border-gray-100 rounded-2xl p-6 flex flex-col items-center justify-center gap-4 hover:border-yellow-400 hover:shadow-lg hover:shadow-yellow-400/10 transition-all duration-300">
                        <div class="w-14 h-14 bg-gray-50 rounded-full flex items-center justify-center text-gray-400 group-hover:bg-yellow-400 group-hover:text-gray-900 transition-colors">
                            <i data-lucide="${getCategoryIcon(cat)}" class="w-7 h-7"></i>
                        </div>
                        <span class="font-bold text-gray-900 text-sm group-hover:text-yellow-600 transition-colors">${cat}</span>
                    </div>
                `).join('')}
             </div>
        </div>

        <!-- Featured Section -->
        <div class="mb-16 fade-in">
             <h2 class="text-3xl font-bold text-gray-900 tracking-tight mb-8">Featured Products</h2>
             <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                ${featured.map(p => createProductCard(p)).join('')}
             </div>
        </div>
    `;

    container.innerHTML = html;
    startHeroSlider();
    refreshIcons();
}

function getCategoryIcon(cat) {
    const icons = {
        'Graphics Cards': 'monitor-play',
        'Processors': 'cpu',
        'Storage': 'hard-drive',
        'Peripherals': 'keyboard',
        'Motherboards': 'server',
        'Cases': 'box',
        'Cooling': 'fan',
        'Memory': 'memory-stick'
    };
    return icons[cat] || 'package';
}

function renderProductsPage(filterQuery = '') {
    const container = document.getElementById('app-content');
    
    let displayProducts = products;
    if (filterQuery) {
        const lower = filterQuery.toLowerCase();
        displayProducts = products.filter(p => 
            p.name.toLowerCase().includes(lower) || 
            p.category.toLowerCase().includes(lower) ||
            p.description.toLowerCase().includes(lower)
        );
    }

    container.innerHTML = `
        <div class="fade-in">
            <div class="flex flex-col md:flex-row justify-between items-end md:items-center mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 tracking-tight">All Products</h1>
                    <p class="text-gray-500 mt-1">Found ${displayProducts.length} items</p>
                </div>
                
                <div class="relative w-full md:w-64">
                    <input type="text" id="page-search" value="${filterQuery}" placeholder="Filter products..." class="w-full bg-white border border-gray-200 rounded-xl py-2.5 pl-10 pr-4 focus:outline-none focus:border-yellow-400 focus:ring-4 focus:ring-yellow-400/10 transition-all text-sm">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3.5 top-3"></i>
                </div>
            </div>

            ${displayProducts.length === 0 ? `
                <div class="text-center py-20 bg-white rounded-3xl border border-dashed border-gray-200">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                        <i data-lucide="search-x" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">No products found</h3>
                    <p class="text-gray-500">Try adjusting your search terms</p>
                    <button onclick="renderProductsPage('')" class="mt-4 text-yellow-600 font-semibold hover:underline">Clear Search</button>
                </div>
            ` : `
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    ${displayProducts.map(p => createProductCard(p)).join('')}
                </div>
            `}
        </div>
    `;

    const searchInput = document.getElementById('page-search');
    if(searchInput) {
        searchInput.addEventListener('input', (e) => {
            renderProductsPage(e.target.value);
            const newInput = document.getElementById('page-search');
            newInput.focus();
            newInput.setSelectionRange(newInput.value.length, newInput.value.length);
        });
    }

    refreshIcons();
}

function renderCartPage() {
    const container = document.getElementById('app-content');
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

    if (cart.length === 0) {
        container.innerHTML = `
            <div class="flex flex-col items-center justify-center py-24 fade-in">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6 text-gray-400">
                    <i data-lucide="shopping-cart" class="w-10 h-10"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Your cart is empty</h2>
                <p class="text-gray-500 mb-8">Looks like you haven't added anything yet.</p>
                <a href="#products" class="bg-yellow-400 text-gray-900 px-8 py-3 rounded-xl font-bold hover:bg-yellow-300 transition-colors shadow-lg shadow-yellow-400/20">
                    Start Shopping
                </a>
            </div>
        `;
    } else {
        container.innerHTML = `
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 fade-in">
                <!-- Cart Items -->
                <div class="lg:col-span-2 space-y-4">
                    <h1 class="text-2xl font-bold text-gray-900 mb-6">Shopping Cart (${cart.length})</h1>
                    ${cart.map(item => `
                        <div class="bg-white p-4 rounded-2xl border border-gray-100 flex gap-4 items-center shadow-sm">
                            <div class="w-20 h-20 bg-gray-50 rounded-xl flex-shrink-0 overflow-hidden">
                                <img src="${item.imageUrl}" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-gray-900 truncate">${item.name}</h3>
                                <p class="text-sm text-gray-500">${item.category}</p>
                                <div class="text-yellow-600 font-bold mt-1">${formatPrice(item.price)}</div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex items-center bg-gray-50 rounded-lg border border-gray-200">
                                    <button onclick="updateCartQuantity(${item.id}, -1)" class="w-8 h-8 flex items-center justify-center hover:bg-gray-200 rounded-l-lg text-gray-600">-</button>
                                    <span class="w-8 text-center text-sm font-semibold">${item.quantity}</span>
                                    <button onclick="updateCartQuantity(${item.id}, 1)" class="w-8 h-8 flex items-center justify-center hover:bg-gray-200 rounded-r-lg text-gray-600">+</button>
                                </div>
                                <button onclick="removeFromCart(${item.id})" class="p-2 text-gray-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-colors">
                                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                                </button>
                            </div>
                        </div>
                    `).join('')}
                </div>

                <!-- Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-soft sticky top-24">
                        <h3 class="font-bold text-gray-900 text-lg mb-6">Order Summary</h3>
                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between text-gray-500 text-sm">
                                <span>Subtotal</span>
                                <span>${formatPrice(total)}</span>
                            </div>
                            <div class="flex justify-between text-gray-500 text-sm">
                                <span>Shipping</span>
                                <span class="text-green-600 font-medium">Free</span>
                            </div>
                            <div class="flex justify-between text-gray-500 text-sm">
                                <span>Tax (Estimated)</span>
                                <span>${formatPrice(total * 0.08)}</span>
                            </div>
                            <div class="h-px bg-gray-100 my-4"></div>
                            <div class="flex justify-between text-gray-900 font-bold text-lg">
                                <span>Total</span>
                                <span>${formatPrice(total * 1.08)}</span>
                            </div>
                        </div>
                        <button onclick="alert('Checkout Simulated! Thank you for testing Nexus Components.')" class="w-full bg-gray-900 text-white py-3.5 rounded-xl font-bold hover:bg-gray-800 transition-colors flex items-center justify-center gap-2 shadow-lg shadow-gray-900/20">
                            Proceed to Checkout <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                        <p class="text-xs text-center text-gray-400 mt-4"><i data-lucide="lock" class="w-3 h-3 inline mr-1"></i> Secure Checkout Simulation</p>
                    </div>
                </div>
            </div>
        `;
    }
    refreshIcons();
}

function renderContactPage() {
    const container = document.getElementById('app-content');
    container.innerHTML = `
        <div class="max-w-2xl mx-auto py-8 fade-in">
            <div class="text-center mb-10">
                <h1 class="text-3xl font-bold text-gray-900 mb-3">Contact Support</h1>
                <p class="text-gray-500">Have questions about a build? We're here to help.</p>
            </div>

            <div id="contact-success" class="hidden bg-green-50 border border-green-100 text-green-800 p-6 rounded-2xl text-center mb-8">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3 text-green-600">
                    <i data-lucide="check" class="w-6 h-6"></i>
                </div>
                <h3 class="font-bold text-lg">Message Sent!</h3>
                <p class="text-sm opacity-80 mt-1">We've received your message and will get back to you shortly.</p>
                <div id="sent-message-preview" class="mt-4 p-4 bg-white/50 rounded-xl text-sm italic border border-green-100/50"></div>
                <button onclick="window.location.reload()" class="mt-4 text-xs font-bold uppercase tracking-wide hover:underline">Send Another</button>
            </div>

            <form id="contact-form" class="bg-white p-8 rounded-3xl border border-gray-100 shadow-soft space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-sm font-bold text-gray-900">Name</label>
                        <input type="text" id="contact-name" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-yellow-400 focus:bg-white transition-colors" placeholder="zaki">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-bold text-gray-900">Email</label>
                        <input type="email" id="contact-email" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-yellow-400 focus:bg-white transition-colors" placeholder="zaki@example.com">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-900">Subject</label>
                    <select class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-yellow-400 focus:bg-white transition-colors">
                        <option>Order Inquiry</option>
                        <option>Product Support</option>
                        <option>Custom Build Help</option>
                        <option>Other</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-900">Message</label>
                    <textarea id="contact-message" required rows="5" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-yellow-400 focus:bg-white transition-colors" placeholder="How can we help you?"></textarea>
                </div>
                <button type="submit" class="w-full bg-gray-900 text-white py-4 rounded-xl font-bold hover:bg-gray-800 transition-colors shadow-lg shadow-gray-900/20">
                    Send Message
                </button>
            </form>
        </div>
    `;
    refreshIcons();

    document.getElementById('contact-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const msg = document.getElementById('contact-message').value;
        document.getElementById('contact-form').style.display = 'none';
        document.getElementById('contact-success').classList.remove('hidden');
        document.getElementById('sent-message-preview').textContent = `"${msg}"`;
    });
}

function renderAdminPage() {
    const container = document.getElementById('app-content');
    
    if (!isAdminAuthenticated) {
        container.innerHTML = `
            <div class="max-w-md mx-auto py-16 fade-in">
                <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-xl text-center">
                    <div class="w-16 h-16 bg-gray-900 rounded-2xl flex items-center justify-center mx-auto mb-6 text-white shadow-lg shadow-gray-900/20">
                        <i data-lucide="lock" class="w-8 h-8"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Admin Access</h2>
                    <p class="text-gray-500 mb-8 text-sm">Restricted area. Please verify your identity.</p>
                    <form onsubmit="handleAdminLogin(event)" class="space-y-4">
                        <input type="password" id="admin-pass" placeholder="Enter Password (admin123)" class="w-full text-center bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:border-yellow-400 transition-colors">
                        <button type="submit" class="w-full bg-yellow-400 text-gray-900 py-3 rounded-xl font-bold hover:bg-yellow-300 transition-colors">Login</button>
                    </form>
                </div>
            </div>
        `;
        refreshIcons();
        return;
    }

    // Admin Dashboard
    container.innerHTML = `
        <div class="fade-in">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Product Management</h1>
                    <p class="text-gray-500 mt-1">Manage your catalog database</p>
                </div>
                <div class="flex gap-3">
                     <button onclick="showProductModal()" class="bg-gray-900 text-white px-5 py-2.5 rounded-xl font-bold hover:bg-gray-800 flex items-center gap-2 text-sm">
                        <i data-lucide="plus" class="w-4 h-4"></i> Add Product
                    </button>
                    <button onclick="logoutAdmin()" class="bg-white border border-gray-200 text-gray-700 px-5 py-2.5 rounded-xl font-bold hover:bg-gray-50 flex items-center gap-2 text-sm">
                        <i data-lucide="log-out" class="w-4 h-4"></i> Logout
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50 border-b border-gray-200 text-xs uppercase font-bold text-gray-500">
                            <tr>
                                <th class="px-6 py-4">Image</th>
                                <th class="px-6 py-4">Name</th>
                                <th class="px-6 py-4">Category</th>
                                <th class="px-6 py-4">Price</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            ${products.map(p => `
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-3">
                                        <div class="w-10 h-10 rounded-lg bg-gray-100 overflow-hidden">
                                            <img src="${p.imageUrl}" class="w-full h-full object-cover">
                                        </div>
                                    </td>
                                    <td class="px-6 py-3 font-medium text-gray-900">${p.name}</td>
                                    <td class="px-6 py-3">
                                        <span class="px-2 py-1 bg-gray-100 rounded text-xs font-semibold">${p.category}</span>
                                    </td>
                                    <td class="px-6 py-3 font-medium">${formatPrice(p.price)}</td>
                                    <td class="px-6 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button onclick="editProduct(${p.id})" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"><i data-lucide="edit-2" class="w-4 h-4"></i></button>
                                            <button onclick="deleteProduct(${p.id})" class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"><i data-lucide="trash" class="w-4 h-4"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <div id="admin-modal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-[60] hidden flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden animate-slide-up">
                <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 id="modal-title" class="text-lg font-bold text-gray-900">Add Product</h3>
                    <button onclick="closeProductModal()" class="text-gray-400 hover:text-gray-900"><i data-lucide="x" class="w-5 h-5"></i></button>
                </div>
                <form id="product-form" onsubmit="handleProductSubmit(event)" class="p-8 space-y-4">
                    <input type="hidden" id="edit-id">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Product Name</label>
                        <input type="text" id="prod-name" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 focus:border-yellow-400 focus:outline-none">
                    </div>
                     <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Price</label>
                            <input type="number" step="0.01" id="prod-price" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 focus:border-yellow-400 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Category</label>
                            <select id="prod-category" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 focus:border-yellow-400 focus:outline-none">
                                <option>Graphics Cards</option>
                                <option>Processors</option>
                                <option>Motherboards</option>
                                <option>Memory</option>
                                <option>Storage</option>
                                <option>Cases</option>
                                <option>Cooling</option>
                                <option>Peripherals</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Image URL</label>
                        <input type="url" id="prod-image" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 focus:border-yellow-400 focus:outline-none" placeholder="https://...">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Description</label>
                        <textarea id="prod-desc" required rows="3" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 focus:border-yellow-400 focus:outline-none"></textarea>
                    </div>
                    <div class="pt-4">
                        <button type="submit" class="w-full bg-gray-900 text-white font-bold py-3 rounded-xl hover:bg-gray-800 transition-colors">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    `;
    refreshIcons();
}

// Admin Logic
window.handleAdminLogin = function(e) {
    e.preventDefault();
    const pass = document.getElementById('admin-pass').value;
    if (pass === 'admin123') { // Simple simulation
        isAdminAuthenticated = true;
        sessionStorage.setItem('nexus_admin_auth', 'true');
        renderAdminPage();
    } else {
        alert('Invalid Password');
    }
};

window.logoutAdmin = function() {
    isAdminAuthenticated = false;
    sessionStorage.removeItem('nexus_admin_auth');
    renderAdminPage();
};

window.deleteProduct = function(id) {
    if(confirm('Are you sure you want to remove this product?')) {
        products = products.filter(p => p.id !== id);
        saveProducts();
        renderAdminPage();
    }
};

window.showProductModal = function() {
    document.getElementById('product-form').reset();
    document.getElementById('edit-id').value = '';
    document.getElementById('modal-title').textContent = 'Add Product';
    document.getElementById('admin-modal').classList.remove('hidden');
};

window.closeProductModal = function() {
    document.getElementById('admin-modal').classList.add('hidden');
};

window.editProduct = function(id) {
    const p = products.find(prod => prod.id === id);
    if (!p) return;
    
    document.getElementById('edit-id').value = p.id;
    document.getElementById('prod-name').value = p.name;
    document.getElementById('prod-price').value = p.price;
    document.getElementById('prod-category').value = p.category;
    document.getElementById('prod-image').value = p.imageUrl;
    document.getElementById('prod-desc').value = p.description;
    
    document.getElementById('modal-title').textContent = 'Edit Product';
    document.getElementById('admin-modal').classList.remove('hidden');
};

window.handleProductSubmit = function(e) {
    e.preventDefault();
    const idStr = document.getElementById('edit-id').value;
    const name = document.getElementById('prod-name').value;
    const price = parseFloat(document.getElementById('prod-price').value);
    const category = document.getElementById('prod-category').value;
    const imageUrl = document.getElementById('prod-image').value;
    const description = document.getElementById('prod-desc').value;

    if (idStr) {
        // Edit
        const id = parseInt(idStr);
        const index = products.findIndex(p => p.id === id);
        if (index !== -1) {
            products[index] = { ...products[index], name, price, category, imageUrl, description };
        }
    } else {
        // Add
        const newId = products.length > 0 ? Math.max(...products.map(p => p.id)) + 1 : 1;
        products.push({ id: newId, name, price, category, imageUrl, description, isNew: true });
    }

    saveProducts();
    closeProductModal();
    renderAdminPage();
};

// --- 5. HERO SLIDER ---

function startHeroSlider() {
    const slides = [
        {
            image: "https://images.unsplash.com/photo-1593640408182-31c70c8268f5?auto=format&fit=crop&q=80&w=2000",
            title: "RTX 40-Series",
            subtitle: "Beyond Fast",
            desc: "Experience ultra-efficient NVIDIA Ada Lovelace architecture.",
            cta: "Shop GPU",
            link: "#products",
            color: "from-green-500/20"
        },
        {
            image: "https://images.unsplash.com/photo-1555617981-d52f6954302e?auto=format&fit=crop&q=80&w=2000",
            title: "Ryzen 7000",
            subtitle: "Performance Reimagined",
            desc: "The world's most advanced PC processor for gamers and creators.",
            cta: "Shop CPU",
            link: "#products",
            color: "from-orange-500/20"
        },
        {
            image: "https://images.unsplash.com/photo-1605792657660-596af9009e82?auto=format&fit=crop&q=80&w=2000",
            title: "Next Gen Gaming",
            subtitle: "Level Up",
            desc: "Build the ultimate battlestation with premium components.",
            cta: "Shop All",
            link: "#products",
            color: "from-purple-500/20"
        }
    ];

    let currentSlide = 0;
    const container = document.getElementById('hero-container');
    if(!container) return;
    
    const renderSlide = () => {
        const slide = slides[currentSlide];
        container.innerHTML = `
            <img src="${slide.image}" class="absolute inset-0 w-full h-full object-cover opacity-60 transition-transform duration-[8000ms] ease-out scale-100 animate-slow-zoom" />
            <div class="absolute inset-0 bg-gradient-to-r from-gray-900 via-gray-900/60 to-transparent"></div>
            <div class="absolute inset-0 bg-gradient-to-t ${slide.color} to-transparent mix-blend-overlay"></div>
            
            <div class="relative z-10 p-8 md:p-16 h-full flex flex-col justify-center items-start max-w-2xl fade-in">
                <span class="bg-white/10 backdrop-blur-md border border-white/20 text-white text-xs font-bold px-3 py-1.5 rounded-full mb-6 flex items-center gap-1.5 animate-slide-up">
                    <i data-lucide="sparkles" class="w-3 h-3 text-yellow-400"></i> ${slide.subtitle}
                </span>
                <h1 class="text-4xl md:text-7xl font-extrabold text-white mb-6 leading-[1.1] tracking-tight animate-slide-up" style="animation-delay: 100ms">
                    ${slide.title}
                </h1>
                <p class="text-gray-300 text-lg md:text-xl mb-10 leading-relaxed font-light animate-slide-up max-w-md" style="animation-delay: 200ms">${slide.desc}</p>
                <div class="flex gap-4 animate-slide-up" style="animation-delay: 300ms">
                    <a href="${slide.link}" class="bg-yellow-400 text-gray-900 font-bold px-8 py-3.5 rounded-xl hover:bg-yellow-300 hover:scale-105 transition-all flex items-center gap-2 shadow-lg shadow-yellow-400/20">
                        ${slide.cta} <i data-lucide="arrow-right" class="w-5 h-5"></i>
                    </a>
                </div>
            </div>
        `;
        refreshIcons();
    };

    renderSlide();
    heroInterval = setInterval(() => {
        currentSlide = (currentSlide + 1) % slides.length;
        renderSlide();
    }, 6000);
}

// --- 6. ROUTER ---

function handleRoute() {
    const hash = window.location.hash;
    window.scrollTo(0,0);
    
    // Simple Router Switch
    if (hash === '' || hash === '#home') {
        renderHomePage();
    } else if (hash.startsWith('#products')) {
        renderProductsPage();
    } else if (hash === '#cart') {
        renderCartPage();
    } else if (hash === '#contact') {
        renderContactPage();
    } else if (hash === '#admin') {
        renderAdminPage();
    } else {
        renderHomePage();
    }

    // Update Active Nav Link
    document.querySelectorAll('.nav-link, .mobile-link').forEach(link => {
        if(link.getAttribute('href') === hash) {
            link.classList.add('text-yellow-600', 'border-yellow-400');
        } else {
            link.classList.remove('text-yellow-600', 'border-yellow-400');
        }
    });
}

// Initialization
window.addEventListener('hashchange', handleRoute);
window.addEventListener('DOMContentLoaded', () => {
    updateCartBadge();
    handleRoute();
    
    // Search Bar Global Listener
    const globalSearch = document.querySelector('input[placeholder="Search components..."]');
    if(globalSearch) {
        globalSearch.addEventListener('keypress', (e) => {
            if(e.key === 'Enter') {
                window.location.hash = '#products';
                // Wait for route change then filter
                setTimeout(() => renderProductsPage(e.target.value), 100);
            }
        });
    }

    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', toggleMobileMenu);
    }
});
