import { Component, Input, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TranslateModule } from '@ngx-translate/core';
import { ActivatedRoute, Router } from '@angular/router';
import { StorageService } from 'app/core/storage/storage.service';

@Component({
  selector: 'app-successful-popup',
  standalone: true,
  imports: [CommonModule, TranslateModule],
  templateUrl: './successful-popup.component.html',
  styleUrls: ['./successful-popup.component.scss']
})
export class SuccessfulPopupComponent implements OnInit {
  @Input() title = '';
  @Input() message = '';
  @Input() buttonLabel = '';

  constructor(
    private router: Router,
    private route: ActivatedRoute,
    public storageService: StorageService
  ) { }

  loginRoutes: Record<'ar' | 'en', string> = {
    ar: 'ar/تسجيل-الدخول',
    en: 'en/login'
  };

  ngOnInit(): void {
    const qp = this.route.snapshot.queryParamMap;
    this.title = this.title || qp.get('title') || '';
    this.message = this.message || qp.get('message') || '';
    this.buttonLabel = this.buttonLabel || qp.get('buttonLabel') || '';
  }

  goToLogin(): void {
    this.router.navigateByUrl('/');
  }
}