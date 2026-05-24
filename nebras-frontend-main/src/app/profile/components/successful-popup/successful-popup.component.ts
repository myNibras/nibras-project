import { Component, Input, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TranslateModule } from '@ngx-translate/core';

@Component({
  selector: 'app-successful-popup',
  standalone: true,
  imports: [CommonModule, TranslateModule],
  templateUrl: './successful-popup.component.html',
  styleUrls: ['./successful-popup.component.scss']
})
export class SuccessfulPopupComponent {
  @Input() title: string = '';
  @Input() message: string = '';
  @Output() close = new EventEmitter<void>(); // 👈 allow parent to close

  onBackdropClick(event: MouseEvent) {
    if ((event.target as HTMLElement).id === 'success-backdrop') {
      this.close.emit();
    }
  }
}
