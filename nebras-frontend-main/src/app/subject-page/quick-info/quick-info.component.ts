import { Component, Input } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';

@Component({
  selector: 'app-quick-info',
  imports: [TranslateModule],
  templateUrl: './quick-info.component.html',
  styleUrl: './quick-info.component.scss'
})
export class QuickInfoComponent {
  @Input() teacher!: string | undefined;
  @Input() duration!: string | undefined;
  @Input() schedule!: string | undefined;
}
