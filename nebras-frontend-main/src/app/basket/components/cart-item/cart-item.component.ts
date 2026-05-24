import { Component, EventEmitter, Input, Output } from '@angular/core';
import { CommonModule } from '@angular/common';
import { CartItem } from 'app/shared/models/payment.model';
import { TranslateModule, TranslateService } from '@ngx-translate/core';

@Component({
  selector: 'app-cart-item',
  imports : [CommonModule, TranslateModule],  
  standalone: true,
  templateUrl: './cart-item.component.html',
})
export class CartItemComponent {
  @Input() item!: CartItem;
  @Input() couponApplied: boolean = false;
  @Output() remove = new EventEmitter<number>();

  constructor(public translate: TranslateService) {}

  onRemove() {
    this.remove.emit(this.item.id);
  }

  get isMonthlyPayment(): boolean {
    return this.item.payment_type === 'monthly';
  }

  get monthlyAmount(): number | undefined {
    const amount = this.item.course.monthly_amount;
    if (amount === undefined || amount === null) {
      return undefined;
    }
    if (typeof amount === 'string') {
      const parsed = parseFloat(amount);
      return isNaN(parsed) ? undefined : parsed;
    }
    return amount;
  }

  get numberOfMonths(): number | undefined {
    return this.item.course.semester_months;
  }

  get isRTL(): boolean {
    return (this.translate.currentLang || 'en') === 'ar';
  }

  formatPrice(price: string | number | undefined): string {
    if (!price) return '0';
    const numPrice = typeof price === 'string' ? parseFloat(price) : price;
    if (isNaN(numPrice)) return String(price);
    
    // Check if it's a whole number
    if (numPrice % 1 === 0) {
      return numPrice.toString();
    }
    
    // Otherwise return with decimals
    return numPrice.toFixed(2);
  }

  get displayPrice(): string {
    // Always use discount_price if it has a value, regardless of coupon status
    const discountPrice = this.item.course.discount_price;
    if (discountPrice && parseFloat(discountPrice) > 0) {
      return this.formatPrice(discountPrice);
    }
    // Otherwise use the original price
    return this.formatPrice(this.item.course.price);
  }
}
