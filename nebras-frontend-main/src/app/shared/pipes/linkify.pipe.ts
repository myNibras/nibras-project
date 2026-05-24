import { Pipe, PipeTransform, inject } from '@angular/core';
import { DomSanitizer, SafeHtml } from '@angular/platform-browser';

const URL_REGEX = /\b(https?:\/\/[^\s<>"']+|www\.[^\s<>"']+)/gi;
const TRAILING_PUNCT = /[.,;:!?)\]>]/;

@Pipe({ name: 'linkify', standalone: true })
export class LinkifyPipe implements PipeTransform {
  private readonly sanitizer = inject(DomSanitizer);

  transform(value: string | null | undefined): SafeHtml {
    if (!value) return '';
    const escaped = this.escapeHtml(value);
    const html = escaped.replace(URL_REGEX, (match) => {
      let trailing = '';
      while (match.length && TRAILING_PUNCT.test(match.slice(-1))) {
        trailing = match.slice(-1) + trailing;
        match = match.slice(0, -1);
      }
      const href = /^https?:\/\//i.test(match) ? match : 'https://' + match;
      return `<a href="${href}" target="_blank" rel="noopener noreferrer" class="underline">${match}</a>${trailing}`;
    });
    // Input is escaped before any anchor wrapping, so the only HTML present is
    // the anchor tags this pipe constructs — safe to mark as trusted.
    return this.sanitizer.bypassSecurityTrustHtml(html);
  }

  private escapeHtml(s: string): string {
    return s
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }
}
