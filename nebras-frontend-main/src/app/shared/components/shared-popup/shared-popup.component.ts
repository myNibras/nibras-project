import { Component, Input, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TranslateModule } from '@ngx-translate/core';

@Component({
  selector: 'app-shared-popup',
  standalone: true,
  imports: [CommonModule, TranslateModule],
  templateUrl: './shared-popup.component.html',
  styleUrls: ['./shared-popup.component.scss']
})
export class SharedPopupComponent {
  @Input() title: string = '';
  @Input() message: string = '';
  @Input() showLoginButton = false;   // 👈 control whether login button appears
  @Input() showCartButton = false;    // 👈 when true, show "Go to basket" instead of Close (redirect to cart)
  @Output() close = new EventEmitter<void>();
  @Output() login = new EventEmitter<void>(); // 👈 notify parent when login is clicked
  @Output() goToCart = new EventEmitter<void>();

  onBackdropClick(event: MouseEvent) {
    if ((event.target as HTMLElement).id === 'shared-backdrop') {
      this.close.emit();
    }
  }
  
}
