import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ArticlesSectionComponent } from './articles-section.component';

describe('ArticlesSectionComponent', () => {
  let component: ArticlesSectionComponent;
  let fixture: ComponentFixture<ArticlesSectionComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ArticlesSectionComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ArticlesSectionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
