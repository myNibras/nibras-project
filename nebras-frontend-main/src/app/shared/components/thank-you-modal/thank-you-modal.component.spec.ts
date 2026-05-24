import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ThankYouModalComponent } from './thank-you-modal.component';

describe('ThankYouModalComponent', () => {
  let component: ThankYouModalComponent;
  let fixture: ComponentFixture<ThankYouModalComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ThankYouModalComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ThankYouModalComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
