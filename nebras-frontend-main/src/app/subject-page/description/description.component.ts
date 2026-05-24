import { Component, Input } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
@Component({
  selector: 'app-description',
  imports: [TranslateModule],
  templateUrl: './description.component.html',
  styleUrl: './description.component.scss'
})
export class DescriptionComponent {

  @Input() description!: string | undefined;
}
