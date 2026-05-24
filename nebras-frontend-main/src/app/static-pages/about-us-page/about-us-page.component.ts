import { Component } from '@angular/core';
import { FaqComponent } from 'app/shared/components/faq/faq.component';
import { TranslateModule } from '@ngx-translate/core';
import { environment } from 'environments/environment';
@Component({
  selector: 'app-about-us-page',
  imports: [FaqComponent, TranslateModule],
  templateUrl: './about-us-page.component.html',
  styleUrl: './about-us-page.component.scss'
})
export class AboutUsPageComponent {
  faqLimit: number | null = environment.faqLimit;
}
