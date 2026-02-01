const modals = document.querySelectorAll('[data-modal]');

function openModal(id) {
  const modal = document.querySelector(`[data-modal="${id}"]`);
  if (modal) {
    modal.classList.add('active');
  }
}

function closeModal(modal) {
  modal.classList.remove('active');
}

modals.forEach((modal) => {
  modal.addEventListener('click', (event) => {
    if (event.target === modal || event.target.closest('.modal-close')) {
      closeModal(modal);
    }
  });
});

document.querySelectorAll('[data-open-modal]').forEach((button) => {
  button.addEventListener('click', () => openModal(button.dataset.openModal));
});

function setupQuantityPickers() {
  document.querySelectorAll('[data-qty-picker]').forEach((picker) => {
    const input = picker.querySelector('input');
    const step = parseInt(picker.dataset.step || '1', 10);

    picker.querySelectorAll('button').forEach((button) => {
      button.addEventListener('click', () => {
        const direction = button.dataset.direction;
        let value = parseInt(input.value || step, 10);
        if (direction === 'minus') {
          value = Math.max(step, value - step);
        } else {
          value += step;
        }
        input.value = value;
        updateCartTotals();
      });
    });

    input?.addEventListener('change', () => {
      let value = parseInt(input.value || step, 10);
      if (Number.isNaN(value) || value < step) {
        value = step;
      }
      value = Math.ceil(value / step) * step;
      input.value = value;
      updateCartTotals();
    });
  });
}

function setupQuestionToggles() {
  document.querySelectorAll('[data-question]').forEach((item) => {
    item.querySelector('button').addEventListener('click', () => {
      item.classList.toggle('active');
    });
  });
}

function updateCartTotals() {
  const totalEl = document.querySelector('[data-cart-total]');
  if (!totalEl) return;
  let subtotal = 0;
  document.querySelectorAll('[data-cart-item]').forEach((row) => {
    const price = parseFloat(row.dataset.price || '0');
    const qtyInput = row.querySelector('input[name^="cart_qty"]');
    const qty = parseInt(qtyInput?.value || '0', 10);
    subtotal += price * qty;
    const lineTotal = row.querySelector('[data-line-total]');
    if (lineTotal) {
      lineTotal.textContent = `${price * qty} DZD`;
    }
  });

  const shipping = getShippingCost();
  const subtotalEl = document.querySelector('[data-cart-subtotal]');
  const shippingEl = document.querySelector('[data-cart-shipping]');
  if (subtotalEl) subtotalEl.textContent = `${subtotal} DZD`;
  if (shippingEl) shippingEl.textContent = `${shipping} DZD`;
  totalEl.textContent = `${subtotal + shipping} DZD`;
}

function getShippingCost() {
  const wilayaSelect = document.querySelector('[data-wilaya-select]');
  const deliverySelect = document.querySelector('[data-delivery-select]');
  if (!wilayaSelect || !deliverySelect) return 0;
  const selected = wilayaSelect.options[wilayaSelect.selectedIndex];
  const domicile = parseInt(selected.dataset.domicile || '0', 10);
  const stopdesk = parseInt(selected.dataset.stopdesk || '0', 10);
  return deliverySelect.value === 'domicile' ? domicile : stopdesk;
}

function setupShippingCalculator() {
  const wilayaSelect = document.querySelector('[data-wilaya-select]');
  const deliverySelect = document.querySelector('[data-delivery-select]');
  if (!wilayaSelect || !deliverySelect) return;
  wilayaSelect.addEventListener('change', updateCartTotals);
  deliverySelect.addEventListener('change', updateCartTotals);
  updateCartTotals();
}

function setupAdminModals() {
  const orderData = window.__ORDERS__ || [];
  const productData = window.__PRODUCTS__ || [];
  const questionData = window.__QUESTIONS__ || [];

  document.querySelectorAll('[data-view-order]').forEach((button) => {
    button.addEventListener('click', () => {
      const orderId = parseInt(button.dataset.viewOrder, 10);
      const order = orderData.find((item) => item.id === orderId);
      if (!order) return;
      const container = document.querySelector('[data-order-details]');
      container.innerHTML = '';
      const list = document.createElement('div');
      list.innerHTML = `
        <p><strong>Client:</strong> ${order.customer.full_name}</p>
        <p><strong>Téléphone:</strong> ${order.customer.phone}</p>
        <p><strong>Adresse:</strong> ${order.customer.wilaya} - ${order.customer.commune}</p>
        <p><strong>Livraison:</strong> ${order.delivery.type} (${order.delivery.price} DZD)</p>
        <p><strong>IP:</strong> ${order.ip}</p>
        <h4>Produits</h4>
        <ul>
          ${order.items.map((item) => `<li>${item.title} x ${item.qty}</li>`).join('')}
        </ul>
      `;
      container.appendChild(list);
      openModal('order-details');
    });
  });

  document.querySelectorAll('[data-edit-product]').forEach((button) => {
    button.addEventListener('click', () => {
      const productId = parseInt(button.dataset.editProduct, 10);
      const product = productData.find((item) => item.id === productId);
      if (!product) return;
      const form = document.querySelector('[data-product-form]');
      form.querySelector('[name="product_id"]').value = product.id;
      form.querySelector('[name="title"]').value = product.title;
      form.querySelector('[name="description"]').value = product.description;
      form.querySelector('[name="price"]').value = product.price;
      form.querySelector('[name="min_qty"]').value = product.min_qty;
      form.querySelector('[name="order"]').value = product.order;
      form.querySelector('[name="images"]').value = (product.images || []).join('\n');
      form.querySelector('[name="options"]').value = (product.options || [])
        .map((option) => `${option.name}: ${option.values.join(', ')}`)
        .join('\n');
      openModal('product-editor');
    });
  });

  document.querySelectorAll('[data-edit-question]').forEach((button) => {
    button.addEventListener('click', () => {
      const questionId = parseInt(button.dataset.editQuestion, 10);
      const question = questionData.find((item) => item.id === questionId);
      if (!question) return;
      const form = document.querySelector('[data-question-form]');
      form.querySelector('[name="question_id"]').value = question.id;
      form.querySelector('[name="question"]').value = question.question;
      form.querySelector('[name="answer"]').value = question.answer;
      form.querySelector('[name="instagram_url"]').value = question.instagram_url || '';
      openModal('question-editor');
    });
  });
}

setupQuantityPickers();
setupQuestionToggles();
setupShippingCalculator();
setupAdminModals();
