import { TranslateModule } from '@ngx-translate/core';
import { Component } from '@angular/core';

@Component({
  selector: 'app-contact-us',
  imports: [TranslateModule],
  templateUrl: './contact-us.component.html',
  styleUrl: './contact-us.component.scss',
  host: { id: 'contact-us' }
})

export class ContactUsComponent {

}
