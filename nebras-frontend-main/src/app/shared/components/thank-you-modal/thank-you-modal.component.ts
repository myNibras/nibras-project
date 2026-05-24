import {
  Component,
  EventEmitter,
  HostListener,
  Input,
  Output
} from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { NgIf } from '@angular/common';
@Component({
  selector: 'app-thank-you-modal',
  standalone: true,
  imports: [NgIf, TranslateModule],
  templateUrl: './thank-you-modal.component.html',
  styleUrl: './thank-you-modal.component.scss'
})
export class ThankYouModalComponent {
  /** Control visibility from parent */
  @Input() open = false;

  /** Content */
  @Input() title = 'Thank you for choosing Nibras Educational Platform!';
  @Input() message = `Your request has been received successfully, and we’re excited to have you on board.
Get ready for an inspiring learning journey. Every great achievement begins with one step, and you’ve just taken it! 🌟`;

  /** CTA */
  @Input() primaryLabel = 'Back To Home';

  /** Optional: show a close (X) in the top-right */
  @Input() showClose = true;

  /** RTL support (bind to your language service if needed) */
  @Input() isRTL = false;

  /** Outputs */
  @Output() primary = new EventEmitter<void>();
  @Output() closed = new EventEmitter<void>();

  /** Close on ESC */
  @HostListener('document:keydown.escape')
  onEsc() {
    if (this.open) this.close();
  }

  close() {
    this.closed.emit();
  }

  onPrimary() {
    this.primary.emit();
  }

  /** click backdrop to close */
  onBackdropClick(e: MouseEvent) {
    // ignore clicks originating from the panel itself
    if ((e.target as HTMLElement).id === 'ty-backdrop') this.close();
  }
}
