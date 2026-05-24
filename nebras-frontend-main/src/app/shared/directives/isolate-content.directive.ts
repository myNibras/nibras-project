import {
  Directive,
  ElementRef,
  Input,
  OnChanges,
  OnInit,
  SimpleChanges,
  SecurityContext,
} from '@angular/core';
import { DomSanitizer } from '@angular/platform-browser';

/**
 * Renders HTML content inside a Shadow DOM so that global/project CSS
 * does not affect it. Use for CMS/rich-text content that should be
 * visually isolated from the rest of the app.
 */
@Directive({
  selector: '[appIsolateContent]',
  standalone: true,
})
export class IsolateContentDirective implements OnInit, OnChanges {
  @Input() appIsolateContent: string | null | undefined = '';

  private shadowRoot: ShadowRoot | null = null;
  private container: HTMLElement | null = null;

  constructor(
    private el: ElementRef<HTMLElement>,
    private sanitizer: DomSanitizer
  ) {}

  ngOnInit(): void {
    this.render();
  }

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['appIsolateContent'] && this.shadowRoot) {
      this.render();
    }
  }

  private render(): void {
    const host = this.el.nativeElement;

    if (!this.shadowRoot) {
      this.shadowRoot = host.attachShadow({ mode: 'open' });
      this.container = document.createElement('div');
      this.container.className = 'isolated-content';
      this.shadowRoot.appendChild(this.container);

      const style = document.createElement('style');
      style.textContent = `
        .isolated-content {
          all: initial;
          display: block;
          max-width: 100%;
          color: #043458;
          line-height: 1.625;
          font-size: 1.125rem;
          font-family: inherit;
          box-sizing: border-box;
        }
        .isolated-content * {
          box-sizing: border-box;
        }
        .isolated-content img {
          max-width: 100%;
        }
      `;
      this.shadowRoot.appendChild(style);
    }

    if (!this.container) return;

    const raw = this.appIsolateContent ?? '';
    const safe = this.sanitizer.sanitize(SecurityContext.HTML, raw) ?? '';
    this.container.innerHTML = safe;
  }
}
