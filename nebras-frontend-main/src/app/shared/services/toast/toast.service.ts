import { Injectable, signal } from '@angular/core';

export type ToastKind = 'success' | 'error' | 'info' | 'warn';

export interface Toast {
  id: number;
  message: string;
  title?: string;
  kind: ToastKind;
}

@Injectable({ providedIn: 'root' })
export class ToastService {
  private _toasts = signal<Toast[]>([]);
  toasts = this._toasts.asReadonly();
  private nextId = 1;

  show(message: string, kind: ToastKind = 'info', title?: string, durationMs = 4500): number {
    const id = this.nextId++;
    this._toasts.update(list => [...list, { id, message, title, kind }]);
    if (durationMs > 0) {
      setTimeout(() => this.dismiss(id), durationMs);
    }
    return id;
  }

  success(message: string, title?: string, durationMs?: number) {
    return this.show(message, 'success', title, durationMs);
  }

  error(message: string, title?: string, durationMs?: number) {
    return this.show(message, 'error', title, durationMs);
  }

  info(message: string, title?: string, durationMs?: number) {
    return this.show(message, 'info', title, durationMs);
  }

  warn(message: string, title?: string, durationMs?: number) {
    return this.show(message, 'warn', title, durationMs);
  }

  dismiss(id: number) {
    this._toasts.update(list => list.filter(t => t.id !== id));
  }

  clear() {
    this._toasts.set([]);
  }
}
