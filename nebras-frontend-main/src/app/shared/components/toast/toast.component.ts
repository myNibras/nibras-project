import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ToastService } from 'app/shared/services/toast/toast.service';

@Component({
  selector: 'app-toast',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './toast.component.html',
  styleUrl: './toast.component.scss'
})
export class ToastComponent {
  toastService = inject(ToastService);

  trackById = (_: number, t: { id: number }) => t.id;
}
